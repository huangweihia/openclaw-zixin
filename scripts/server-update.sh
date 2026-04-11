#!/usr/bin/env bash
set -euo pipefail

# One-click server update（Docker，与 docker-compose.server.yml 配套）:
# - 拉起容器、修正 storage 权限
# - git pull / reset 到远程分支
# - composer install（失败时可自动补 lock：见下方 COMPOSER_ON_INSTALL_FAIL）
# - filament:upgrade（已安装 Filament 时）
# - npm ci + build（node:20-alpine 一次性容器）
# - migrate + 部分 seed + Laravel 缓存
#
# Usage:
#   cd /path/to/repo && bash scripts/server-update.sh
#
# 环境变量：
#   COMPOSER_ON_INSTALL_FAIL=update|abort  默认 update：install 失败时先尝试只更新 Filament 子树，仍失败则 composer update --no-dev
#   DEPLOY_GIT_STRATEGY=merge              仅快进合并，不 reset --hard
#   REMOTE / BRANCH                        默认 origin / main
#
# Git：默认 fetch 后 reset --hard 到 origin/main。
#
# Auto detect repository root:
# 1) REPO_DIR env (if provided)
# 2) parent directory of this script
# 3) current working directory

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
DEFAULT_REPO_DIR="$(cd -- "$SCRIPT_DIR/.." >/dev/null 2>&1 && pwd)"
REPO_DIR="${REPO_DIR:-$DEFAULT_REPO_DIR}"
COMPOSE_FILE="docker-compose.server.yml"
REMOTE="${REMOTE:-origin}"
BRANCH="${BRANCH:-main}"
DEPTH="${DEPTH:-1}"
FILTER="${FILTER:-blob:none}"

cd "$REPO_DIR"

if [ ! -f "artisan" ] || [ ! -f "$COMPOSE_FILE" ]; then
  echo "Error: invalid Laravel repo directory: $REPO_DIR"
  echo "Please run this script from repo root, or set REPO_DIR explicitly."
  exit 1
fi

echo "[0/5] ensure containers are up..."
docker compose -f "$COMPOSE_FILE" up -d

echo "[0b/5] fix storage & bootstrap/cache permissions (avoid Blade Permission denied)…"
docker compose -f "$COMPOSE_FILE" exec -T --user 0 php sh -lc 'mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/framework/testing storage/logs storage/app/public bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache' || true

echo "[1/5] pull latest code..."
# Optimization for slow servers:
# - protocol v2 + partial clone blob filter to reduce transfer
# - depth=1 shallow fetch by default
git config --local protocol.version 2 >/dev/null 2>&1 || true
git fetch --prune --no-tags --filter="$FILTER" --depth="$DEPTH" "$REMOTE" "$BRANCH"

# 默认与远程完全一致，避免 merge --ff-only 在「无关历史 / 强推 / 分叉」时失败，导致代码从未更新却继续 build。
# 仅快进合并：  DEPLOY_GIT_STRATEGY=merge bash scripts/server-update.sh
DEPLOY_GIT_STRATEGY="${DEPLOY_GIT_STRATEGY:-reset}"
if [ "$DEPLOY_GIT_STRATEGY" = "merge" ]; then
  echo "git fast-forward merge (DEPLOY_GIT_STRATEGY=merge)..."
  git merge --ff-only FETCH_HEAD
else
  echo "git reset --hard ${REMOTE}/${BRANCH} (丢弃服务器上未推送的本地提交，与 GitHub 一致)..."
  git reset --hard "${REMOTE}/${BRANCH}"
fi

echo "[2/5] install PHP deps (composer)..."
COMPOSER_ON_INSTALL_FAIL="${COMPOSER_ON_INSTALL_FAIL:-update}"
composer_install() {
  docker compose -f "$COMPOSE_FILE" exec -T php composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
}
if composer_install; then
  :
else
  _ci=$?
  if [ "$COMPOSER_ON_INSTALL_FAIL" != "update" ]; then
    echo "composer install failed ($_ci). 已设置 COMPOSER_ON_INSTALL_FAIL=abort，退出。详见 docs/admin-filament-first-deploy.md"
    exit "$_ci"
  fi
  echo "composer install failed ($_ci)。常见原因：composer.lock 未包含 composer.json 中的新依赖（如 Filament）。"
  echo "尝试：composer update filament/filament --with-all-dependencies …"
  if ! docker compose -f "$COMPOSE_FILE" exec -T php composer update filament/filament --with-all-dependencies --no-interaction --no-dev --prefer-dist --optimize-autoloader; then
    echo "定向更新仍失败，执行 composer update --no-dev（会按约束刷新全部依赖 lock，生产环境请事后将 lock 提交仓库以便下次仅 install）。"
    docker compose -f "$COMPOSE_FILE" exec -T php composer update --no-interaction --no-dev --prefer-dist --optimize-autoloader
  fi
fi

echo "[2b/5] Filament 前端资源（已安装时）..."
if docker compose -f "$COMPOSE_FILE" exec -T php php artisan help filament:upgrade >/dev/null 2>&1; then
  docker compose -f "$COMPOSE_FILE" exec -T php php artisan filament:upgrade --no-interaction
else
  echo "(跳过 filament:upgrade：当前未安装 Filament 或命令不可用)"
fi

echo "[3/5] build frontend assets (Vite)..."
echo "using node:20-alpine to build..."
docker run --rm -v "$REPO_DIR":/app -w /app node:20-alpine sh -lc "npm ci && npm run build"

echo "[4/5] run migrate (no data reset) + refresh laravel caches..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan migrate --force

echo "seed personality quiz if empty..."
# Safe: the seeder exits early when data already exists.
docker compose -f "$COMPOSE_FILE" exec -T php php artisan db:seed --class=PersonalityQuizSeeder --force || true

docker compose -f "$COMPOSE_FILE" exec -T php php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:cache
echo "[5/5] Done: code pulled, deps OK, migrated, caches refreshed. 后台：见 .env 中 ADMIN_PATH_PREFIX / ADMIN_DOMAIN。"

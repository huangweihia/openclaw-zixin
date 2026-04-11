#!/usr/bin/env bash
set -euo pipefail

# 日常发布：拉代码 + composer install + 前端构建 + 迁移 + 缓存
# （不包含「install 失败自动 composer update」与每次 filament:upgrade，避免每次发布都动依赖树）
#
# Usage:
#   cd /path/to/repo && bash scripts/server-update.sh
#
# 环境变量：
#   DEPLOY_GIT_STRATEGY=merge   仅快进合并，不 reset --hard
#   REMOTE / BRANCH             默认 origin / main
#
# 若 composer install 因 lock 与 json 不一致失败：在仓库根目录执行一次
#   bash scripts/server-composer-filament-once.sh
# 详见 docs/admin-filament-first-deploy.md
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

echo "[0/4] ensure containers are up..."
docker compose -f "$COMPOSE_FILE" up -d

echo "[0b/4] fix storage & bootstrap/cache permissions (avoid Blade Permission denied)…"
docker compose -f "$COMPOSE_FILE" exec -T --user 0 php sh -lc 'mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/framework/testing storage/logs storage/app/public bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache' || true

echo "[1/4] pull latest code..."
git config --local protocol.version 2 >/dev/null 2>&1 || true
git fetch --prune --no-tags --filter="$FILTER" --depth="$DEPTH" "$REMOTE" "$BRANCH"

DEPLOY_GIT_STRATEGY="${DEPLOY_GIT_STRATEGY:-reset}"
if [ "$DEPLOY_GIT_STRATEGY" = "merge" ]; then
  echo "git fast-forward merge (DEPLOY_GIT_STRATEGY=merge)..."
  git merge --ff-only FETCH_HEAD
else
  echo "git reset --hard ${REMOTE}/${BRANCH} (丢弃服务器上未推送的本地提交，与 GitHub 一致)..."
  git reset --hard "${REMOTE}/${BRANCH}"
fi

echo "[2/4] install PHP deps (composer install only)..."
# Composer 在 **php 镜像内**（见 docker/php/Dockerfile），不是单独容器：docker compose exec php composer …
docker compose -f "$COMPOSE_FILE" exec -T php composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

echo "[3/4] build frontend assets (Vite)..."
echo "using node:20-alpine to build..."
docker run --rm -v "$REPO_DIR":/app -w /app node:20-alpine sh -lc "npm ci && npm run build"

echo "[4/4] run migrate (no data reset) + refresh laravel caches..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan migrate --force

echo "seed personality quiz if empty..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan db:seed --class=PersonalityQuizSeeder --force || true

docker compose -f "$COMPOSE_FILE" exec -T php php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:cache
# 勿用 route:cache：与 Filament/Livewire 并存时，易出现「登录 POST /admin/login → 405」（应用仅注册 GET，提交应由 Livewire 走 /livewire/update）
docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:clear
# view:cache 易与 Filament/Livewire 动态 Blade 冲突，保持视图不预编译缓存
docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:clear
echo "Done: code pulled, composer install, migrated, caches refreshed."

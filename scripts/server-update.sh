#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# 日常发布：拉代码 + composer + 前端构建 + 迁移 + 缓存 + schedule:run（一次）
# 不含：install 失败自动 composer update、每次 filament:upgrade（避免动依赖树）
#
# Usage:
#   cd /path/to/repo && bash scripts/server-update.sh
#
# 环境变量：
#   REPO_DIR                    仓库根（默认：本脚本上一级目录）
#   DEPLOY_GIT_STRATEGY=merge   仅快进合并，不 reset --hard
#   REMOTE / BRANCH             默认 origin / main
#   DEPTH / FILTER              浅克隆 + partial clone
#   HTTPS_PROXY / https_proxy   拉 GitHub 时代理，例：HTTPS_PROXY=http://127.0.0.1:7890 bash ...
#
# composer lock 不一致时先执行：bash scripts/server-composer-filament-once.sh
# 详见 docs/admin-filament-first-deploy.md
# =============================================================================

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
DEFAULT_REPO_DIR="$(cd -- "$SCRIPT_DIR/.." >/dev/null 2>&1 && pwd)"
REPO_DIR="${REPO_DIR:-$DEFAULT_REPO_DIR}"
COMPOSE_FILE="docker-compose.server.yml"
REMOTE="${REMOTE:-origin}"
BRANCH="${BRANCH:-main}"
DEPTH="${DEPTH:-1}"
FILTER="${FILTER:-blob:none}"

# --- 输出格式 -----------------------------------------------------------------
hr() {
  printf '%s\n' "──────────────────────────────────────────────────────────────────────"
}

title() {
  printf '\n'
  hr
  printf '  %s\n' "$1"
  hr
  printf '\n'
}

step() {
  printf '[%s/%s] %s\n' "$1" "$2" "$3"
}

TOTAL_STEPS="9"

# php 容器内执行 artisan（与下方 composer 一致用服务名 php）
artisan() {
  docker compose -f "$COMPOSE_FILE" exec -T php php artisan "$@"
}

# =============================================================================

cd "$REPO_DIR"

if [ ! -f "artisan" ] || [ ! -f "$COMPOSE_FILE" ]; then
  echo "Error: invalid Laravel repo directory: $REPO_DIR" >&2
  echo "  Need: artisan + $COMPOSE_FILE" >&2
  exit 1
fi

title "server-update — $(basename "$REPO_DIR")"

step "1" "$TOTAL_STEPS" "Docker Compose：启动/更新容器"
docker compose -f "$COMPOSE_FILE" up -d

step "2" "$TOTAL_STEPS" "权限：storage、bootstrap/cache（www-data）"
docker compose -f "$COMPOSE_FILE" exec -T --user 0 php sh -lc \
  'mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/framework/testing storage/logs storage/app/public bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache' \
  || true

step "3" "$TOTAL_STEPS" "Git：拉取 ${REMOTE}/${BRANCH}"
git config --local protocol.version 2 >/dev/null 2>&1 || true
git fetch --prune --no-tags --filter="$FILTER" --depth="$DEPTH" "$REMOTE" "$BRANCH"

DEPLOY_GIT_STRATEGY="${DEPLOY_GIT_STRATEGY:-reset}"
if [ "$DEPLOY_GIT_STRATEGY" = "merge" ]; then
  echo "  → fast-forward merge (DEPLOY_GIT_STRATEGY=merge)"
  git merge --ff-only FETCH_HEAD
else
  echo "  → reset --hard（丢弃服务器未推送提交）"
  git reset --hard "${REMOTE}/${BRANCH}"
fi

step "4" "$TOTAL_STEPS" "Composer：install（生产、无 dev）"
# Composer 在 php 镜像内，见 docker/php/Dockerfile
docker compose -f "$COMPOSE_FILE" exec -T php composer install \
  --no-interaction --no-dev --prefer-dist --optimize-autoloader

step "5" "$TOTAL_STEPS" "前端：npm ci + build（node:20-alpine 一次性容器）"
docker run --rm -v "$REPO_DIR":/app -w /app node:20-alpine sh -lc "npm ci && npm run build"

step "6" "$TOTAL_STEPS" "数据库：migrate --force"
artisan migrate --force

echo "  → 可选种子 PersonalityQuizSeeder（失败忽略）"
artisan db:seed --class=PersonalityQuizSeeder --force || true

step "7" "$TOTAL_STEPS" "Laravel 缓存：optimize:clear → config:cache → route/view clear"
artisan optimize:clear
artisan config:cache
# 勿 route:cache：Filament/Livewire 下易出现 admin 登录 405
artisan route:clear
# 勿 view:cache：与动态 Blade 易冲突
artisan view:clear

step "8" "$TOTAL_STEPS" "调度：schedule:run（本分钟到期任务执行一次）"
# 注意：set -e 下用 if ! 包裹，避免「某个定时任务失败」导致整次发布脚本非 0 退出
if ! artisan schedule:run --no-interaction; then
  echo "  WARNING: schedule:run 退出码非 0（可能某条调度命令失败）。发布其余步骤已完成。" >&2
fi

step "9" "$TOTAL_STEPS" "完成"
printf '\n'
hr
printf '  OK: 拉代码 | composer | 前端构建 | 迁移 | 缓存 | schedule:run 已执行\n'
printf '  提示: 订阅等需「每分钟」触发时，请在系统加一条 cron，例如：\n'
printf '        * * * * * cd %s && docker compose -f %s exec -T php php artisan schedule:run\n' "$REPO_DIR" "$COMPOSE_FILE"
printf '\n'
hr
printf '\n'

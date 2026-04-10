#!/usr/bin/env bash
set -euo pipefail

# One-click server update:
# - pull latest code
# - install PHP deps (composer)
# - build frontend assets (Vite/Vue)
# - run non-destructive migrations
# - clear/cache optimize for Laravel
#
# Usage:
#   bash /opt/openclaw-zixin/scripts/server-update.sh
#   ./scripts/server-update.sh
#
# Git：默认 fetch 后 reset --hard 到 origin/main。若只想快进合并：DEPLOY_GIT_STRATEGY=merge ./scripts/server-update.sh
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

echo "[1/4] pull latest code..."
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

echo "[2/4] install PHP deps (composer)..."
docker compose -f "$COMPOSE_FILE" exec -T php composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

echo "[3/4] build frontend assets (Vite)..."
echo "using node:20-alpine to build..."
docker run --rm -v "$REPO_DIR":/app -w /app node:20-alpine sh -lc "npm ci && npm run build"

echo "[4/4] run migrate (no data reset) + refresh laravel caches..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan migrate --force

echo "seed personality quiz if empty..."
# Safe: the seeder exits early when data already exists.
docker compose -f "$COMPOSE_FILE" exec -T php php artisan db:seed --class=PersonalityQuizSeeder --force || true

docker compose -f "$COMPOSE_FILE" exec -T php php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:cache
echo "Done: code pulled, migrated, caches refreshed."

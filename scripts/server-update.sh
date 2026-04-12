#!/usr/bin/env bash
set -euo pipefail

# 核心：拉代码 → PHP 迁移 → 清缓存 → 构建前端（Vite）
# 用法：在仓库根执行  bash scripts/server-update.sh
# 环境变量：REPO_DIR、REMOTE、BRANCH、DEPLOY_GIT_STRATEGY=merge、DEPTH、FILTER（同以往）
# 需已 docker compose up（php 容器可 exec），见 docker-compose.server.yml

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
REPO_DIR="${REPO_DIR:-$(cd -- "$SCRIPT_DIR/.." && pwd)}"
COMPOSE_FILE="docker-compose.server.yml"
REMOTE="${REMOTE:-origin}"
BRANCH="${BRANCH:-main}"
DEPTH="${DEPTH:-1}"
FILTER="${FILTER:-blob:none}"

cd "$REPO_DIR"
[[ -f artisan && -f "$COMPOSE_FILE" ]] || { echo "Need artisan + $COMPOSE_FILE in $REPO_DIR" >&2; exit 1; }

artisan() { docker compose -f "$COMPOSE_FILE" exec -T php php artisan "$@"; }

echo "[1/4] git pull ${REMOTE}/${BRANCH}"
git config --local protocol.version 2 >/dev/null 2>&1 || true
git fetch --prune --no-tags --filter="$FILTER" --depth="$DEPTH" "$REMOTE" "$BRANCH"
if [ "${DEPLOY_GIT_STRATEGY:-reset}" = "merge" ]; then
  git merge --ff-only FETCH_HEAD
else
  git reset --hard "${REMOTE}/${BRANCH}"
fi

echo "[2/4] php artisan migrate --force"
artisan migrate --force

echo "[3/4] php artisan optimize:clear"
artisan optimize:clear

echo "[4/4] npm ci && npm run build"
docker run --rm -v "$REPO_DIR":/app -w /app node:20-alpine sh -lc "npm ci && npm run build"

echo "Done."

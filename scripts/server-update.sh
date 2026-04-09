#!/usr/bin/env bash
set -euo pipefail

# One-click server update:
# - pull latest code
# - run non-destructive migrations
# - clear/cache optimize for Laravel
#
# Usage:
#   bash /opt/openclaw-zixin/scripts/server-update.sh
#   ./scripts/server-update.sh
#
# Auto detect repository root:
# 1) REPO_DIR env (if provided)
# 2) parent directory of this script
# 3) current working directory

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
DEFAULT_REPO_DIR="$(cd -- "$SCRIPT_DIR/.." >/dev/null 2>&1 && pwd)"
REPO_DIR="${REPO_DIR:-$DEFAULT_REPO_DIR}"
COMPOSE_FILE="docker-compose.server.yml"

cd "$REPO_DIR"

if [ ! -f "artisan" ] || [ ! -f "$COMPOSE_FILE" ]; then
  echo "Error: invalid Laravel repo directory: $REPO_DIR"
  echo "Please run this script from repo root, or set REPO_DIR explicitly."
  exit 1
fi

echo "[1/3] pull latest code..."
# Optimization for slow servers:
# - protocol v2 + partial clone blob filter to reduce transfer
# - depth=1 shallow fetch by default (override with GIT_DEPTH=0 to disable)
REMOTE="${GIT_REMOTE:-origin}"
BRANCH="${GIT_BRANCH:-main}"
DEPTH="${GIT_DEPTH:-1}"
FILTER="${GIT_FILTER:-blob:none}"

if [ "$DEPTH" = "0" ]; then
  echo "git fetch (full) from $REMOTE $BRANCH..."
  git -c protocol.version=2 fetch --prune --no-tags "$REMOTE" "$BRANCH"
else
  echo "git fetch (depth=$DEPTH, filter=$FILTER) from $REMOTE $BRANCH..."
  git -c protocol.version=2 fetch --prune --no-tags --filter="$FILTER" --depth="$DEPTH" "$REMOTE" "$BRANCH"
fi

echo "git fast-forward merge..."
git merge --ff-only FETCH_HEAD

echo "[2/3] run migrate (no data reset)..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan migrate --force

echo "[3/3] refresh laravel caches..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:cache
echo "Done: code pulled, migrated, caches refreshed."

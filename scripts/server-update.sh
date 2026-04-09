#!/usr/bin/env bash
set -euo pipefail

# One-click server update:
# - pull latest code
# - run non-destructive migrations
# - rebuild frontend assets
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

echo "[1/7] pull latest code..."
git pull --ff-only origin main

echo "[2/7] ensure containers are running..."
docker compose -f "$COMPOSE_FILE" up -d

echo "[3/7] install PHP dependencies..."
docker compose -f "$COMPOSE_FILE" exec -T php composer install --no-dev --optimize-autoloader --no-interaction

echo "[4/7] run migrate (no data reset)..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan migrate --force

echo "[5/7] build frontend assets..."
if docker compose -f "$COMPOSE_FILE" exec -T php sh -lc "command -v npm >/dev/null 2>&1"; then
  docker compose -f "$COMPOSE_FILE" exec -T php npm ci
  docker compose -f "$COMPOSE_FILE" exec -T php npm run build
else
  docker run --rm -v "$PWD:/app" -w /app node:20-alpine sh -lc "npm ci && npm run build"
fi

echo "[6/7] refresh laravel caches..."
docker compose -f "$COMPOSE_FILE" exec -T php php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T php php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan route:cache
docker compose -f "$COMPOSE_FILE" exec -T php php artisan view:cache

echo "[7/7] restart php/nginx..."
docker compose -f "$COMPOSE_FILE" restart php nginx

echo "Done: code updated, migrated, assets built, caches refreshed."

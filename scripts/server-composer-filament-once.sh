#!/usr/bin/env bash
set -euo pipefail

# 按需执行（非每次发布）：当 composer.json 新增了依赖而仓库里 composer.lock 未同步，导致
#   server-update.sh 里 composer install 失败
# 时，在 **已 cd 到仓库根** 且 Docker 已起来的情况下运行本脚本。
#
# 会做：
#   1) 确保 php 容器运行
#   2) composer install；失败则依次尝试 filament 定向 update、再全量 composer update --no-dev
#   3) 若存在命令则 php artisan filament:upgrade
#
# Usage:
#   cd /path/to/repo && bash scripts/server-composer-filament-once.sh
#
# 成功后建议：将服务器上更新后的 composer.lock 提交回 Git，以后日常只需 server-update.sh。

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
REPO_DIR="${REPO_DIR:-$(cd -- "$SCRIPT_DIR/.." && pwd)}"
COMPOSE_FILE="docker-compose.server.yml"

cd "$REPO_DIR"
if [ ! -f "artisan" ] || [ ! -f "$COMPOSE_FILE" ]; then
  echo "Error: invalid Laravel repo directory: $REPO_DIR"
  exit 1
fi

echo "[0/2] ensure php container is up..."
docker compose -f "$COMPOSE_FILE" up -d php

echo "[1/2] composer: install or recover lock..."
composer_install() {
  docker compose -f "$COMPOSE_FILE" exec -T php composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
}
if composer_install; then
  echo "composer install OK."
else
  echo "composer install failed. Trying: composer update filament/filament --with-all-dependencies …"
  if ! docker compose -f "$COMPOSE_FILE" exec -T php composer update filament/filament --with-all-dependencies --no-interaction --no-dev --prefer-dist --optimize-autoloader; then
    echo "Targeted update failed. Running: composer update --no-dev …"
    docker compose -f "$COMPOSE_FILE" exec -T php composer update --no-interaction --no-dev --prefer-dist --optimize-autoloader
  fi
fi

echo "[2/2] Filament assets (if installed)..."
if docker compose -f "$COMPOSE_FILE" exec -T php php artisan help filament:upgrade >/dev/null 2>&1; then
  docker compose -f "$COMPOSE_FILE" exec -T php php artisan filament:upgrade --no-interaction
else
  echo "(skip: filament:upgrade not available)"
fi

echo "Done. Consider copying composer.lock back to git for reproducible deploys."

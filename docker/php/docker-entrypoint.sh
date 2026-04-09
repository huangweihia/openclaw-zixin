#!/bin/sh
set -e
cd /var/www/html || exit 1

# 宿主机代码卷里的 bootstrap/cache/*.php 若来自「带 dev 依赖」的 package:discover，
# 而本容器 vendor 为 --no-dev（无 Collision / Ignition 等），会导致
# Class "...CollisionServiceProvider" not found，连 artisan 都无法执行。
# 启动时删掉包清单缓存，由 Laravel 按当前 vendor 内实际安装的包重新生成。
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php 2>/dev/null || true

# 上传文件写入 storage/app/public，对外 URL 为 /storage/...，依赖 public/storage → ../storage/app/public
mkdir -p public storage/app/public 2>/dev/null || true
if [ ! -L public/storage ]; then
    rm -rf public/storage
    ln -sfn ../storage/app/public public/storage
fi

# 命名卷 zhixin-vendor 挂载到 vendor 时，首次为空，会导致 vendor/autoload.php 不存在
if [ ! -f vendor/autoload.php ]; then
    echo "[openclaw] vendor/autoload.php 缺失，正在执行 composer install（首次或清空卷后必现一次）…"
    export COMPOSER_ALLOW_SUPERUSER=1
    # 先正常安装；若锁文件与当前环境不一致或平台检查误报，再忽略平台约束（与本地 PHP 8.2 一致时仍应能装齐）
    if ! composer install --no-interaction --prefer-dist --no-progress --no-ansi; then
        echo "[openclaw] composer install 失败，重试（--ignore-platform-reqs）…"
        composer install --no-interaction --prefer-dist --no-progress --no-ansi --ignore-platform-reqs
    fi
fi

exec docker-php-entrypoint "$@"

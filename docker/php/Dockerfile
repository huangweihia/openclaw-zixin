FROM php:8.2-fpm

# Composer（命名卷挂载 vendor 时须在容器内执行 composer install）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install common PHP extensions required by Laravel.
# Keep it minimal so container build doesn't require extra internet beyond apt.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    gd \
    intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# 挂载卷内仓库属主与容器用户不一致时，Composer 可能调用 git；在镜像内统一放行（避免写在 entrypoint）
RUN git config --global --add safe.directory '*'

COPY docker-entrypoint.sh /usr/local/bin/openclaw-php-entrypoint.sh
RUN chmod +x /usr/local/bin/openclaw-php-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/openclaw-php-entrypoint.sh"]
CMD ["php-fpm"]

# Filament 首次上线：服务器上要做的 Composer 步骤

仓库里的 `composer.json` 已声明 `filament/filament`，但 **若 Git 里尚未包含已安装 Filament 后的 `composer.lock`**，在服务器上直接执行 `composer install` 会报错（lock 与 json 不一致）。

## 推荐流程（在服务器仓库根目录）

1. 拉代码：`git pull`（或先跑 `server-update.sh` 里会失败在 composer 一步时再处理依赖）。

2. **补全依赖并发布 Filament 资源（首次 / lock 不同步时）**，任选其一：
   - **推荐一键**：`bash scripts/server-composer-filament-once.sh`
   - **或手动**（在 php 容器内）：

     ```bash
     docker compose -f docker-compose.server.yml exec -T php composer require filament/filament:"^3.3" -W --no-interaction --no-dev --prefer-dist
     ```

     已声明 Filament 时也可用：`composer update --no-interaction --no-dev --prefer-dist`，再执行 `php artisan filament:upgrade --no-interaction`。

3. **把新的 `composer.lock` 提交回 Git**（推荐，以后 `server-update.sh` 里只需 `composer install`）。

4. 日常完整发版：`bash scripts/server-update.sh`（前端构建、迁移、缓存等）。

5. 浏览器访问：
   - 路径模式：`https://你的域名/admin/login`（与 `ADMIN_PATH_PREFIX` 一致，默认 `admin`）。
   - 独立后台域名：根路径登录页（由 `AdminPanelProvider` 的 `->domain()` 决定）。

6. 使用 **`users` 表中 `role = admin` 且未封禁** 的账号登录（与旧 Vue 后台同一套账号逻辑）；首次进入会自动补一条 `admin_users` 记录（与旧 `AuthController` 行为一致）。

## 之后日常部署

`composer.lock` 已包含 Filament 后，**只跑 `server-update.sh`** 即可；其中只有 `composer install`，**不会**每次执行 `composer update` 或 `filament:upgrade`。

## 服务器上 `git pull` / `git fetch` 很慢

国内机房访问 **GitHub** 经常慢或抖动，和项目大小无关时也多半是网络问题。可按优先级尝试：

1. **用脚本里已有的优化**（`server-update.sh` 已默认）：`git fetch` 使用 **`--depth=1`**（浅历史）+ **`--filter=blob:none`**（partial clone，少拉大文件），比完整 `git pull` 轻量。若你是多年前在服务器上 **完整克隆** 的仓库，可备份 `.env` 后 **重新浅克隆** 再拷回 `.env`：  
   `git clone --depth 1 --branch main --single-branch https://github.com/你的用户/openclaw-zixin.git`

2. **只对 Git 走 HTTP 代理**（服务器上若有 Clash 等）：  
   `HTTPS_PROXY=http://127.0.0.1:7890 bash scripts/server-update.sh`  
   或长期仅 GitHub：  
   `git config --global http.https://github.com.proxy http://127.0.0.1:7890`

3. **改用 SSH 远程**（`git@github.com:...`）：在部分网络下比 HTTPS 更稳；需配好服务器上的 **SSH key** 并加到 GitHub。

4. **镜像仓**：把 GitHub 仓库同步到 **Gitee / 自建 Git**，服务器 `remote` 指向镜像（注意镜像延迟与权限）。

5. **少用大对象**：避免把备份、大资源提交进 Git；否则即使用 partial clone，首次仍可能慢。

不推荐依赖第三方「GitHub 加速 URL」改 remote 地址：易失效、有安全风险。

## 脚本分工

| 脚本 | 何时用 |
|------|--------|
| `scripts/server-update.sh` | 每次发版：拉代码、`composer install`、前端构建、迁移、缓存。 |
| `scripts/server-composer-filament-once.sh` | **按需**：首次引入 Filament / lock 与 json 不一致导致 `install` 失败，或升级 Filament 大版本后需要 `filament:upgrade` 时。 |

**说明**：没有单独的「Composer 容器」。`docker/php/Dockerfile` 已把 Composer 装进 **php** 镜像，因此用 `docker compose exec php composer …` 即可（对应你面板里的 `openclaw-zixin-php-1` 这类容器，服务名是 `php`）。

## Composer 报「requirements could not be resolved」且列出大量 Filament 版本

常见原因是 **PHP 缺少 `intl` 扩展**（Filament 依赖链需要）。

### 方式 A：当前容器里快速补上 intl（不等镜像构建）

适合「先让 Composer 跑通」，**容器被删重建后需重做**；长期仍建议方式 B 与 Dockerfile 一致。

若 `apt-get update` 访问 `deb.debian.org` **极慢**（国内机房常见），先在容器内把 Debian 源换成国内镜像再装（下面示例用**阿里云**，也可改成清华 `mirrors.tuna.tsinghua.edu.cn` 等）：

```bash
docker compose -f docker-compose.server.yml exec --user 0 php sh -lc '
set -e
replace_apt_mirrors() {
  sed -i "s|deb.debian.org|mirrors.aliyun.com|g; s|security.debian.org/debian-security|mirrors.aliyun.com/debian-security|g" "$1"
}
[ -f /etc/apt/sources.list ] && replace_apt_mirrors /etc/apt/sources.list || true
[ -f /etc/apt/sources.list.d/debian.sources ] && replace_apt_mirrors /etc/apt/sources.list.d/debian.sources || true
apt-get update
apt-get install -y --no-install-recommends libicu-dev $PHPIZE_DEPS
docker-php-ext-install intl
'
docker compose -f docker-compose.server.yml restart php
docker compose -f docker-compose.server.yml exec -T php php -m | grep intl
```

不换源、网络又差时，**仅 `apt-get update` 就可能要十几分钟**，与是否 Docker 无关。

### 方式 B：用仓库里的 Dockerfile 重建镜像（日常不要加 `--no-cache`）

`git pull` 后执行（**默认不要** `--no-cache`，否则会强制整镜像重跑 apt，非常慢；仅怀疑构建缓存损坏时再临时加）：

```bash
docker compose -f docker-compose.server.yml build php
docker compose -f docker-compose.server.yml up -d
bash scripts/server-composer-filament-once.sh
```

## 登录页能打开，点登录报 405：POST admin/login 不允许

Filament 登录由 **Livewire** 处理，正常提交应请求 **`/livewire/update`**，而不是传统表单 POST 到 `/admin/login`。出现 405 多半是 **Livewire 的 JS 没在浏览器里跑起来**（表单退化成整页 POST）。

### 必查（HTTPS / 反代）

1. **`.env` 里 `APP_URL`** 必须与浏览器地址一致，含 **`https://` 与正确域名**（不要用 `http://` 访问却配 `https`，或相反）。
2. **`TrustProxies`**：仓库已默认 `protected $proxies = '*'`（Docker 内 Nginx→PHP 常见）。否则 Laravel 认不出 HTTPS，生成的脚本地址会变成 `http://`，浏览器拦截 **混合内容** → Livewire 不加载。
3. **Nginx→PHP**：仓库 `docker/nginx/conf.d/00-forwarded-map.conf` + `fastcgi_param HTTP_X_FORWARDED_PROTO` 会把外层 **`X-Forwarded-Proto`** 传给 PHP；若你自建 Nginx，请对照补上。改完后 **`docker compose up -d --force-recreate nginx`**（或等价重启）。
4. 服务器执行：`php artisan route:clear`、`php artisan config:clear`、`php artisan view:clear`。
5. 浏览器 **强制刷新**；开发者工具 **Network** 看 **`livewire.js`** 是否 200、**Console** 是否无红色报错。

# Filament 首次上线：服务器上要做的 Composer 步骤

仓库里的 `composer.json` 已声明 `filament/filament`，但 **若 Git 里尚未包含已安装 Filament 后的 `composer.lock`**，在服务器上直接执行 `composer install` 会报错（lock 与 json 不一致）。

## 推荐流程（在服务器仓库根目录）

1. 拉代码：`git pull`（或通过 `server-update.sh` 内已带的 fetch/reset）。

2. **在 php 容器内更新依赖并生成 lock**（任选其一）：

   ```bash
   docker compose -f docker-compose.server.yml exec -T php composer require filament/filament:"^3.3" -W --no-interaction --no-dev --prefer-dist
   ```

   或（已含 filament 声明时）：

   ```bash
   docker compose -f docker-compose.server.yml exec -T php composer update --no-interaction --no-dev --prefer-dist
   ```

3. **把新的 `composer.lock` 提交回 Git**（推荐，这样其他环境与其他部署机可直接 `composer install`）。

4. 继续执行 `scripts/server-update.sh`（或手动：`npm run build`、`migrate`、`optimize:clear` 等）。

5. 发布 Filament 前端资源（升级包后建议执行）：

   ```bash
   docker compose -f docker-compose.server.yml exec -T php php artisan filament:upgrade --no-interaction
   ```

6. 浏览器访问：
   - 路径模式：`https://你的域名/admin/login`（与 `ADMIN_PATH_PREFIX` 一致，默认 `admin`）。
   - 独立后台域名：根路径登录页（由 `AdminPanelProvider` 的 `->domain()` 决定）。

7. 使用 **`users` 表中 `role = admin` 且未封禁** 的账号登录（与旧 Vue 后台同一套账号逻辑）；首次进入会自动补一条 `admin_users` 记录（与旧 `AuthController` 行为一致）。

## 之后日常部署

`composer.lock` 已包含 Filament 后，**只跑 `server-update.sh` 中的 `composer install` 即可**，无需每次 `require`。

## 与 `server-update.sh` 的关系

- 默认会先执行 `composer install`；若因 **lock 与 json 不一致** 失败，且未设置 `COMPOSER_ON_INSTALL_FAIL=abort`，脚本会依次尝试：
  1. `composer update filament/filament --with-all-dependencies`
  2. 仍失败则 `composer update --no-dev`（刷新整份 lock，**建议随后将服务器上新生成的 `composer.lock` 提交回 Git**，以后部署可稳定 `install`）。
- 依赖就绪后会自动执行 `php artisan filament:upgrade`（仅当该 Artisan 命令存在时）。

若你希望 **install 失败直接退出**、不在服务器上改 lock：`COMPOSER_ON_INSTALL_FAIL=abort bash scripts/server-update.sh`。

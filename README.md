# OpenClaw 智信（Laravel + Vue）

> **本目录即 Laravel 应用根**（含 `docker/`、`docker-compose.yml`）。  
> **Git 仓库建议在本目录执行 `git init`**；服务器部署时也可直接同步本目录内容，无需再改挂载路径。
>
> **把“内容站”升级成“可变现的增长系统”**：内容分发 → 互动沉淀 → 会员分层 → 支付转化 → 触达复购。

[![Laravel](https://img.shields.io/badge/Laravel-10-ff2d20.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4.svg)](https://www.php.net)
[![Vue](https://img.shields.io/badge/Vue-3-42b883.svg)](https://vuejs.org)
[![Vite](https://img.shields.io/badge/Vite-5-646cff.svg)](https://vitejs.dev)
[![Element Plus](https://img.shields.io/badge/Element%20Plus-Admin-409eff.svg)](https://element-plus.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ed.svg)](https://docs.docker.com/compose/)

OpenClaw 智信是一个面向“**内容变现 + 私域运营 + 会员增长**”的一体化站点：前台承载内容分发与转化，后台提供内容运营、审核、触达与数据闭环能力。

## ✨ 亮点（更像产品，而不是 Demo）

- **内容 × 会员 × 运营一体化**：不是“发文章”，而是“从流量到付费”的可运营闭环
- **可持续增长工具箱**：UGC 投稿、评论、收藏、审核、举报、积分、订阅、推送、邮件
- **漂亮且可扩展的管理端**：Vue + Element Plus，支持菜单/按钮级权限（RBAC）
- **可观测性**：OpenClaw 任务日志对外上报 + 管理端检索筛选 + 统计图表

## 📚 项目文档（同仓库内置）

项目文档在本目录下的 `docs/`：

- 开发环境配置：`docs/01-开发环境配置.md`
- 数据库设计：`docs/02-数据库表字段详细设计.md`

## 快速开始（Windows + Docker，推荐）

先进入本目录（与 `docker-compose.yml` 同级）：

```bash
cd D:\lewan\openclaw-data\workspace\openclaw_zhixin\_laravel_temp
```

```bash
copy .env.example .env
docker compose up -d

docker compose exec -T php composer install
docker compose exec -T php npm install

docker compose exec -T php php artisan key:generate
docker compose exec -T php php artisan migrate
docker compose exec -T php php artisan db:seed

docker compose exec -T php npm run build
```

访问：

- **前台**：`http://localhost:8083`
- **后台**：`http://localhost:8083/admin`

## 服务器部署

将本目录整体同步到服务器（例如 `/opt/zixin/openclaw-zixin`），保证 **`artisan`、`docker/`、`docker-compose.server.yml` 在同一层**，然后：

```bash
cd /opt/zixin/openclaw-zixin
docker compose -f docker-compose.server.yml up -d --build
```

# OpenClaw 智信（Laravel + Vue）

> 内容变现 + 私域运营 + 会员增长的一体化项目骨架。  
> 本目录即 Laravel 项目根目录（`artisan`、`docker/`、`docker-compose*.yml` 同级）。

## 项目亮点

- 内容中心：文章、案例、工具、项目、付费资源统一管理
- 增长闭环：UGC（投稿/评论/收藏） + 会员体系（VIP/SVIP） + 订单支付
- 管理后台：Vue 3 + Element Plus，权限与运营模块完整
- 可观测性：任务日志、审核流程、通知触达（站内信/邮件）

## 技术栈

- 后端：PHP 8.2 + Laravel 10
- 前端：Vue 3 + Vite + Element Plus + Blade + Tailwind
- 数据：MySQL 8 + Redis 7
- 部署：Docker + Docker Compose

## 项目文档

为了避免命令混用，文档已拆分为两个版本：

- **本地开发文档**：`docs/01-开发环境配置.md`
- **服务器部署文档**：`docs/02-服务器部署配置.md`
- 数据库设计：`docs/02-数据库表字段详细设计.md`

## 快速导航

- 本地开发：按 `docs/01-开发环境配置.md` 执行（开发环境可用 `npm run dev`）
- 服务器部署：按 `docs/02-服务器部署配置.md` 执行（生产环境只用 `npm run build`）

## 目录结构（简化）

```text
app/                # 业务代码（控制器/模型/服务）
database/           # 迁移、Seeder、测试数据
resources/          # Blade、Vue、样式资源
public/             # Web 入口与静态资源
docker/             # PHP/Nginx 容器配置
docs/               # 本地/服务器文档与设计文档
docker-compose.yml
docker-compose.server.yml
artisan
```

## 说明

- 生产环境请务必设置：
  - `APP_ENV=production`
  - `APP_DEBUG=false`
- `.env` 不提交到 Git；本地和服务器请分别维护。

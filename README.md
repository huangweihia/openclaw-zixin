# OpenClaw 智信 · 内容变现与私域运营引擎

> **定位**：帮助内容团队、社区产品和创业者，用一套「站点 + 会员 + 运营后台」完成从流量到收入的闭环。  
> **场景**：教程站 / 案例库 / AI 工具导航 / 社区 / 会员制内容产品。

---

## 🌟 这个项目解决了什么问题？

- **不仅是“内容站”，而是“增长与变现系统”**
  - 内容承载：文章、案例、工具、项目、付费资源统一管理
  - 转化路径：公开内容 → UGC 互动 → 会员权益 → 支付订阅
- **把运营动作产品化**
  - 评论 / 收藏 / 投稿 / 举报 / 审核流程
  - 站内信、邮件通知、系统消息、任务日志
  - VIP / SVIP 分层、积分、付费资源、SVIP 自定义订阅
- **给运营和技术团队一个「可落地的起点」**
  - 已有完整的数据库结构、接口、管理后台和文档
  - 可以按业务快速裁剪或扩展，而不是从 0 搭框架

---

## 🧩 核心业务模块概览

| 模块 | 说明 | 价值 |
|------|------|------|
| 内容中心 | 文章、项目、案例（副业案例）、AI 工具、付费资源 | 承载 SEO 与流量，形成内容资产 |
| 会员体系 | 免费用户 / VIP / SVIP、多种权益组合 | 支持阶梯收费与高价值用户运营 |
| 支付与订单 | 订阅、一次性付费、退款申请、发票申请 | 打通从浏览到付费的闭环 |
| UGC 与互动 | 用户投稿、评论、点赞、收藏、浏览历史 | 提升留存与社区活跃度 |
| 审核与风控 | 投稿审核、评论举报、审核日志 | 保证内容质量与合规性 |
| 运营触达 | 站内信、邮件通知、系统消息、任务日志 | 做激活 / 召回 / 复购与问题排查 |
| 私域 SOP | 私域 SOP 配置与展示页 | 把运营动作沉淀为可重复执行的流程 |
| OpenClaw 任务日志 | 任务执行日志 + 统计图表 | 让「系统在干什么」变得可观察 |

> 更细的字段设计、接口和流程说明，见 `docs/02-数据库表字段详细设计.md` 及 `docs/` 目录下的「功能清单」「原型图」等文档。

---

## 🏗 技术栈与架构

- **后端框架**：Laravel 10（PHP 8.2）
- **管理后台**：Vue 3 + Vite + Element Plus
- **前台展示**：Blade + Tailwind + 自定义皮肤（`public/css/skins.css`）
- **数据层**：MySQL 8（主库），Redis 7（缓存 / Session）
- **容器化**：Docker + Docker Compose（本地开发与服务器部署同构）
- **其他**
  - 队列 / 计划任务基于 Laravel 内置能力
  - 邮件基于 Laravel Mail，可接 SMTP（如 QQ 邮箱）
  - 企业微信（WeCom）集成预留配置位

目录结构（简化）：

```text
app/                # 核心业务代码（控制器、模型、服务）
database/           # 迁移 + Seeder + 初始化数据
resources/          # Blade 模板、前端 Vue 代码、样式
public/             # Web 根目录，入口 index.php、构建产物
docker/             # php-fpm + nginx 配置
docs/               # 开发说明、数据库文档、功能清单、原型图
docker-compose*.yml # 本地开发 & 服务器部署编排文件
```

---

## 🚀 如何开始开发 / 部署？

为了避免 README 过于冗长，**所有环境与部署细节统一放在文档里**：

- **开发与本地环境**：`docs/01-开发环境配置.md`
- **数据库设计**：`docs/02-数据库表字段详细设计.md`
- **其他说明**：`docs/` 下的功能清单、原型图、后台与前台对应关系等

你只需要记住两件事：

1. **本目录就是 Laravel 根目录**（`artisan`、`docker/`、`docker-compose.yml` 都在这里）  
2. 任何与「怎么跑起来」「怎么上服务器」相关的问题，先看 `docs/01-开发环境配置.md` 即可

---

## ✅ 项目适合怎样的下一步？

如果你是：

- **产品或运营**：可以直接用现有模块做一个「MVP 收费站点」，再按转化数据迭代
- **技术负责人**：可以把它当成「内容 + 会员 + 运营后台」的脚手架，裁剪或扩展模块
- **投资人 / 合作伙伴**：可以通过代码结构与文档，快速理解项目的商业想象空间和落地深度

后续如果你希望：

- 加上更多支付渠道（微信、支付宝等）  
- 接入更多增长渠道（短信、企微、钉钉等）  
- 做更细的运营看板和报表  

这些都可以在当前架构上自然演进。现在的这套代码，就是为这些增长方向预留好“骨架”的版本。

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

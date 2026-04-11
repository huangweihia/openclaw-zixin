# Filament 后台整体重做 — 方案说明

> 环境基线：`Laravel 10.x` + `PHP 8.1+` → 采用 **Filament v3.3+**（稳定版，与官方要求一致）。  
> Filament v4 / v5 需更高 Laravel / PHP / Tailwind 版本，当前仓库不升级框架前不采纳。  
> 旧后台快照路径：`backup/pre-filament-admin-2026-04-10/`（已含 Vue SPA、admin_api、中间件、Blade、vite 入口等）。

---

## 零、环境与职责：本地 vs 服务器（务必区分）

| 环境 | 典型场景 | 做什么 | 不做什么 / 注意 |
|------|----------|--------|------------------|
| **本地开发机**（如 Windows、未装 PHP/Composer） | 改 PHP/Vue/配置、提交 Git | 编辑代码、Review Diff、必要时仅跑前端 `npm`（若本机有 Node） | **不要求**在本机执行 `composer`、`php artisan`；Cursor/Agent 若无法调用本机 PHP，属正常，以服务器为准 |
| **服务器 / 生产**（含 Docker Compose） | 部署、装依赖、迁移、构建 | `composer install` / `composer update`、`php artisan migrate`、`php artisan optimize:*`、`npm ci && npm run build`；Filament 首次安装后的 `php artisan filament:install`、`filament:upgrade` 等 **一律在服务器（或 php 容器）内执行** | 以仓库内 `scripts/server-update.sh` 为参考流程；在容器内执行：`docker compose -f docker-compose.server.yml exec -T php composer …`、`exec -T php php artisan …` |

**结论**：依赖安装与 Artisan **以服务器（或你们 CI 里带 PHP 的环境）为权威**；本地没有 PHP 不影响开发与合并代码。合并含 `composer.json` 变更的分支后，必须在服务器上跑一次 `composer install`（或让 `server-update.sh` 已包含该步）。

Filament 静态资源：安装/升级包后，在服务器上执行官方推荐的 `php artisan filament:upgrade`（或按 Filament 文档在对应版本下发布资源），再 `optimize:clear` / `view:cache` 视部署策略而定。

---

## 一、与现状的差异（为什么要重做）

| 维度 | 现状（Vue SPA） | Filament |
|------|-----------------|------------|
| UI 技术 | Vue 3 + Element Plus + 自建页面 | Livewire 3 + Alpine + Filament 组件 |
| 路由 | `/admin/*` + `/api/admin/*` | Panel 内 Livewire 路由，一般仍挂 `/admin` |
| 鉴权 | Sanctum Cookie + `admin_users` + 自定义 `perm:*` | 推荐 `admin` guard + Filament `canAccessPanel` + Policy |
| CRUD | 手写 API + 前端表单 | `Resource` 声明式表单/表格，开发量显著下降 |
| 导航 | DB `admin_nav_*` + 前端拉取 | Filament `NavigationGroup` / `NavigationItem`（可选保留 DB 做「菜单可见性」扩展） |

---

## 二、技术决策（实施前需拍板）

1. **Panel 路径**  
   - 默认：`/admin`（与现 `config('admin.path_prefix')` 一致）。  
   - 若使用独立 `ADMIN_DOMAIN`，Filament 的 `->domain()` 与 `->path()` 需与 `RouteServiceProvider` 协调，避免与前台冲突。

2. **登录身份**  
   - **推荐**：Filament 使用 **`admin_users` 表** + 独立 `admin` guard（与现中间件语义一致）。  
   - `User`（前台用户）仅在前台资源里以「关联关系」出现，不作为 Filament 登录主体。

3. **权限模型**  
   - **方案 A**：`admin_permissions` + `Spatie` 风格映射到 Laravel `Gate` / Policy（工作量大但标准）。  
   - **方案 B（过渡）**：Filament `canViewAny` 等先按角色硬编码，再迭代接入 DB 权限。  
   - 现有 `AdminBootstrapSeeder` 中的 `admin:*` 键可映射为 Policy 方法名或 Filament Cluster 权限。

4. **旧 API**  
   - 过渡期可保留只读 `api/admin/*` 供脚本使用，**新功能只写 Filament**；稳定后删除 `admin_api.php` 与 `Api\Admin\*`。

5. **前端构建**  
   - Filament 自带资源；`vite.config.js` 可移除 `resources/js/admin/main.js` 入口（实施阶段执行）。

---

## 三、数据表 → Filament 资源映射（依据 migrations）

以下为「建议 Resource / Page」清单。带 ※ 的为更适合 **自定义 Page + Table** 或 **只读 Resource**。

### 3.1 总览

| 功能 | 数据来源 | Filament |
|------|----------|----------|
| 仪表盘 | 多表聚合（现 `DashboardController@stats`） | `Dashboard` + Widgets（图表/统计卡片） |

### 3.2 内容与社区

| 菜单名（中文） | 表 / 主体 | 列表 | 新建 | 编辑 | 删除 | 备注 |
|----------------|-----------|------|------|------|------|------|
| 文章 | `articles` | ✓ | ✓ | ✓ | ✓ | 富文本/HTML；关联 `categories`, `users` |
| 分类 | `categories` | ✓ | ✓ | ✓ | ✓ | |
| 项目 | `projects` | ✓ | ✓ | ✓ | ✓ | 关联 `categories` |
| 评论 | `comments` | ✓ | — | ✓ | ✓ | 隐藏/删除；多态目标 |
| 评论举报 | `comment_reports` | ✓ | — | ✓ | ✓ | 处置状态 |
| 用户动态审核 | `user_posts` | ✓ | — | ✓ | — | 与现「Moderation」对齐 |
| 发布审计 | `publish_audits` | ✓ | — | ※ | — | 只读或状态更新 |

### 3.3 用户与会员

| 菜单名 | 表 | 列表 | 编辑 | 备注 |
|--------|-----|------|------|------|
| 前台用户 | `users` | ✓ | ✓ | 角色、封禁、积分余额等字段以 migration 为准 |
| 积分流水 | `points` | ✓ | — | 只读为主 |
| 浏览记录 | `view_histories` | ✓ | — | 只读 |
| 收藏 | `favorites` | ✓ | — | 可选只读 |
| 用户操作日志 | `user_actions` | ✓ | — | 可选只读 |

### 3.4 订单与收款

| 菜单名 | 表 | 列表 | 编辑 | 备注 |
|--------|-----|------|------|------|
| 订单 | `orders` | ✓ | ✓ | 状态、金额 |
| 订阅 | `subscriptions` | ✓ | ※ | 与业务确认是否可改 |
| SVIP 订阅 | `svip_subscriptions` | ✓ | ※ | |
| SVIP 订阅日志 | `svip_subscription_logs` | ✓ | — | 只读 |
| SVIP 定制订阅 | `svip_custom_subscriptions` | ✓ | — | 现后台只读则 Filament 只读 |
| 退款申请 | `refund_requests` | ✓ | ✓ | 审批流 |
| 发票申请 | `invoice_requests` | ✓ | ✓ | |

### 3.5 资源与增长（会员内容）

| 菜单名 | 表 | 列表 | 新建 | 编辑 | 删除 |
|--------|-----|------|------|------|------|
| 会员资源 | `premium_resources` | ✓ | ✓ | ✓ | ✓ |
| 副业案例 | `side_hustle_cases` | ✓ | ✓ | ✓ | ✓ | 含 `resource_type` / `resource_url` 等 migration 字段 |
| 私域 SOP | `private_traffic_sops` | ✓ | ✓ | ✓ | ✓ |
| AI 工具变现 | `ai_tool_monetization` | ✓ | ✓ | ✓ | ✓ |

### 3.6 营销、通知与邮件

| 菜单名 | 表 | 列表 | 新建 | 编辑 | 删除 | 备注 |
|--------|-----|------|------|------|------|------|
| 公告 | `announcements` | ✓ | ✓ | ✓ | ✓ | 含展示位、浮动等扩展字段 |
| 系统通知 | `system_notifications` | ✓ | ✓ | ✓ | ✓ | 发布/站内推送逻辑保留在 Action |
| 推送记录 | `push_notifications` | ✓ | ✓ | ※ | ✓ | 关联用户 |
| 邮件模板 | `email_templates` | ✓ | ✓ | ✓ | ✓ | 预览可做成 Filament Action |
| 邮件记录 | `email_logs` | ✓ | — | ※ | ※ | 只读 + 详情 Infolist |
| 邮件订阅 | `email_subscriptions` | ✓ | ✓ | ✓ | ✓ | 主题/时间 JSON |
| 邮件配置 | `email_settings` | ✓ | ✓ | ✓ | ✓ | 测试发信 → Action |

### 3.7 站点、外观与广告

| 菜单名 | 表 | 列表 | 编辑 | 备注 |
|--------|-----|------|------|------|
| 站点设置 | `site_settings` | ※ | ✓ | 键值或多行，可用 Simple Page + Form |
| 首页评价 | `site_testimonials` | ✓ | ✓ | ✓ |
| 皮肤配置 | `skin_configs` | ✓ | ✓ | 含私有字段 migration |
| 用户皮肤 | `user_skins` | ✓ | ※ | |
| 广告位 | `ad_slots` | ✓ | ✓ | 兜底图上传沿用现 API 或 Filament FileUpload |

### 3.8 运营、自动化与审计

| 菜单名 | 表 | 列表 | 删除 | 备注 |
|--------|-----|------|------|------|
| OpenClaw 任务日志 | `openclaw_task_logs` | ✓ | ✓ | 统计页 → Widget 或独立 Page |
| 审计日志 | `audit_logs` | ✓ | — | 只读 |
| 站内私信（可选） | `profile_messages` | ✓ | ※ | 视产品是否后台管理 |

### 3.9 人格测试模块

| 菜单名 | 表 | 说明 |
|--------|-----|------|
| 维度 / 题目 / 选项 / 类型 | `personality_dimensions` 等 | 各一张 Resource 或 Cluster |
| 测评设置 | `personality_quiz_settings` | 单例式 Settings Page |
| 测评记录 | `personality_quiz_plays` | 只读列表 + 详情 |

### 3.10 站内通知（Laravel）

| 菜单名 | 表 | 说明 |
|--------|-----|------|
| 用户通知设置 | `user_notification_settings` | 只读或排查用 |
| `notifications` | 数据库通知 | 可选只读列表；与 `system_notifications` 区分 |

### 3.11 后台自身（系统管理）

| 菜单名 | 表 | 说明 |
|--------|-----|------|
| 管理员账号 | `admin_users` | Resource（仅超级管理员可写） |
| 角色 | `admin_roles` | Resource |
| 权限 | `admin_permissions` | 只读或 Seeder 维护 |
| 角色-权限 | `admin_role_permissions` | 在 Role 表单用 `CheckboxList` |
| 用户-角色 | `admin_user_roles` | 在 AdminUser 表单关联 |
| 导航配置 | `admin_nav_sections` / `admin_nav_items` | **可选**：若完全改用 Filament 静态导航，可废弃；若保留则 Resource |

---

## 四、Filament 侧栏导航结构（建议）

以下为 **NavigationGroup** 分组顺序（可按产品再调）：

1. **总览** — Dashboard  
2. **内容与社区** — 文章、分类、项目、评论、评论举报、动态审核、发布审计  
3. **用户与会员** — 用户、积分流水、浏览记录（收藏/操作日志可选）  
4. **订单与财务** — 订单、订阅、SVIP、定制订阅、退款、发票  
5. **资源与增长** — 会员资源、副业案例、私域 SOP、AI 工具变现  
6. **营销与触达** — 公告、系统通知、推送、邮件模板/订阅/记录/配置  
7. **站点与外观** — 站点设置、首页评价、皮肤、用户皮肤、广告位  
8. **运营与自动化** — OpenClaw 日志、审计日志  
9. **人格测试** — 题库与设置、测评记录  
10. **系统** — 管理员、角色、（导航配置）、Filament 自带个人资料  

「演示用」`SharedComponentsShowcase` 不迁移；若需组件演示可做成仅 `local` 可见的 Page。

---

## 五、分阶段实施（建议迭代顺序）

| 阶段 | 内容 | 产出 |
|------|------|------|
| **P0** | `composer require filament/filament:^3.3 -W`；`filament:install --panels`；注册 `AdminPanelProvider`；`admin_users` 登录 | 可登录的空 Panel |
| **P1** | 替换 `RouteServiceProvider` 中 SPA：`/{any}` → Filament；保留 `api/admin` 或标记废弃 | `/admin` 进入 Filament |
| **P2** | 高流量 CRUD：`articles`, `categories`, `projects`, `users`, `comments` | 核心业务可运营 |
| **P3** | 订单域：`orders`, `subscriptions`, `refund_requests`, `invoice_requests` | |
| **P4** | 资源域：`premium_resources`, `side_hustle_cases`, `private_traffic_sops`, `ai_tool_monetization` | |
| **P5** | 邮件与营销、站点设置、皮肤、广告位 | |
| **P6** | `openclaw_task_logs`、审计、人格测试 | |
| **P7** | `admin_roles` / 权限接入 Filament Policy；删除 Vue 入口与冗余 API | 收尾 |

---

## 六、风险与注意事项

- **路由冲突**：必须先下线 `AdminSpaController` 的 catch-all，再挂 Filament。  
- **双域配置**：`ADMIN_DOMAIN` + `APP_FRONT_DOMAIN` 时，Filament `Panel::domain()` 必须与之一致。  
- **上传**：现 `UploadController` 可改为 Filament `FileUpload` + 同一存储盘。  
- **自定义页**：仪表盘统计、邮件预览、OpenClaw 图表等用 **Filament Page + Widget**，不必硬塞进 Resource。

---

## 七、下一步（你确认后写代码）

1. 确认 **P0 鉴权**：仅用 `admin_users`，还是临时兼容某一类前台账号。  
2. 确认 **权限方案**：A（Policy 全量）或 B（先角色后细粒度）。  
3. 按 **P0 → P1** 开分支，提交 Filament 依赖与 Panel 骨架；再按上表逐 Resource 落地。

本文档随实施可继续追加：各 Resource 的 `form()` / `table()` 字段清单可直接链到各 migration 文件备注。

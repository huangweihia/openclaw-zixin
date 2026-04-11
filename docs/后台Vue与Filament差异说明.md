# 旧版 Vue SPA 后台 vs 当前 Filament 后台 — 差异说明

本文对照 `resources/js/admin/router/index.js` 中的 **Vue 路由/页面** 与当前 **Laravel Filament** 实现，便于评估功能覆盖与交互差异。旧版侧栏菜单实际由 `GET /api/admin/nav-menu` 驱动，与路由列表大体一致。

## 1. 菜单与信息架构

| 维度 | Vue 版 | Filament 版 |
|------|--------|----------------|
| 侧栏来源 | 接口动态菜单 + 前端路由表 | `AdminNavItem` + `filament_admin_menu_map` 映射到各 Resource |
| 路由风格 | History 模式 SPA（如 `/articles`） | `/admin/...` 下 Filament 资源路由 |
| 权限 | 前端 `permissions.js` + 路由 `meta.perm` | 后台用户 `allowsAdminMenuKey` + `admin_permissions` |

## 2. 页面级：Vue 有、Filament 无或弱对应

| Vue 路由/页面 | 说明 |
|---------------|------|
| `/moderation` **投稿审核** | 独立工作台式页面。Filament 已增加 **`ModerationHubPage`（审核工作台）**：汇总待审动态/发布审计/评论举报数量并提供快捷入口；明细处理仍在 `UserPostResource` 等列表中完成。 |
| `/shared-components` **公共组件** | 组件展示/调试页；Filament 无等价页。 |

## 3. 页面级：Filament 有、Vue 无或拆分方式不同

| Filament 资源/能力 | 说明 |
|-------------------|------|
| **权限字典** `AdminPermissionResource` | 细粒度权限键管理；Vue 侧为角色+菜单为主，无同名「字典」独立模块。 |
| **人格测评** 拆为多资源（维度/题目/选项/类型/设置等） | Vue 为单页 `PersonalityQuizOps`（SBTI 配置）集中操作；Filament 按 Eloquent 模型拆分，交互路径更长。 |
| **站点设置** `SiteSettingResource` / 皮肤等 | 与 Vue `Settings`、`SkinConfigs` 等有重叠但表单结构可能不一致，需按业务字段逐项核对。 |

## 4. 同一业务：交互与定制差异（概括）

| 模块 | Vue 版常见定制 | Filament 版现状 |
|------|----------------|-----------------|
| **仪表盘** | `Dashboard.vue` 可自由拼图表、卡片 | 以 `AdminStatsOverview` 等 Widget 为主，扩展需新增 PHP Widget。 |
| **文章/项目** | 独立 `ArticleEditor` / `ProjectEditor` 长表单 | Filament 默认 Resource 表单，富文本与媒体流程可能不同。 |
| **广告位** | 列表 + 启用态（截图中单启用） | 已用 `AdSlotObserver` 保证全局仅一个 `is_active`，列表增加 **设为启用** 操作。 |
| **列表列展示** | 常做关联名称、状态中文 | 正逐步将 `user_id` 等改为 `user.name`、订单号等；部分技术型字段仍可能显示 ID。 |
| **保存后跳转** | 前端路由自控 | Resource 的 Create/Edit 页通过 `RedirectsToIndexAfterSave` **保存后回列表**。 |

## 5. Vue 路径 ↔ Filament 能力对照（主数据面）

下表按 `router/index.js` 的 **path** 与当前仓库中的 **Filament Resource / Page** 对应（不含 `/login`）。`menu_key` 列对应 `config/filament_admin_menu_map.php` 中的键（有则侧栏走 `AdminNavItem` + RBAC）。

| Vue path | 典型菜单含义 | Filament 对应 | `menu_key`（若有） |
|----------|----------------|-----------------|-------------------|
| `/` | 仪表盘 | `App\Filament\Pages\Dashboard` + Widget | `dashboard`（需在导航表配置） |
| `/moderation` | 投稿审核 | `UserPostResource` + **`ModerationHubPage`** | `moderation`（列表）/ `moderation-hub`（工作台，见迁移） |
| `/users`、`/users/:id` | 用户管理 / 编辑 | `UserResource` | `users` |
| `/points-ledger` | 积分流水 | `PointResource` | `points-ledger` |
| `/orders` | 订单 | `OrderResource` | `orders` |
| `/subscriptions` | 会员订阅 | `SubscriptionResource` | `subscriptions` |
| `/view-histories` | 浏览历史 | `ViewHistoryResource` | `view-histories` |
| `/svip-custom-subscriptions` | SVIP 定制订阅 | `SvipCustomSubscriptionResource` | `svip-custom-subscriptions` |
| `/articles`、`/articles/create`、`/articles/:id/edit` | 文章 | `ArticleResource` | `articles` |
| `/projects`、… | 项目 | `ProjectResource` | `projects` |
| `/categories` | 分类 | `CategoryResource` | `categories` |
| `/comments` | 评论 | `CommentResource` | `comments` |
| `/settings` | 系统与站点 | `SiteSettingResource`（另有聚合页见下） | `settings` |
| `/email-templates` | 邮件模板 | `EmailTemplateResource` | `email-templates` |
| `/email-logs` | 邮件记录 | `EmailLogResource` | `email-logs` |
| `/email-subscriptions` | 邮箱订阅 | `EmailSubscriptionResource` | `email-subscriptions` |
| `/site-testimonials` | 首页评价 | `SiteTestimonialResource` | `site-testimonials` |
| `/announcements` | 公告 | `AnnouncementResource` | `announcements` |
| `/ad-slots` | 广告位 | `AdSlotResource` | `ad-slots` |
| `/openclaw-task-logs` | OpenClaw 日志 | `OpenclawTaskLogResource` | `openclaw-task-logs` |
| `/premium-resources` | 会员资源 | `MemberPremiumResource` | `premium-resources` |
| `/side-hustle-cases` | 副业案例 | `SideHustleCaseResource` | `side-hustle-cases` |
| `/private-traffic-sops` | 私域 SOP | `PrivateTrafficSopResource` | `private-traffic-sops` |
| `/ai-tool-monetization` | AI 工具变现 | `AiToolMonetizationResource` | `ai-tool-monetization` |
| `/system-notifications` | 系统通知 | `SystemNotificationResource` | `system-notifications` |
| `/skin-configs` | 皮肤主题 | `SkinConfigResource` | `skin-configs` |
| `/email-settings` | 邮件配置 | `EmailSettingResource` | `email-settings` |
| `/push-notifications` | 站内推送 | `PushNotificationResource` | `push-notifications` |
| `/refund-requests` | 退款申请 | `RefundRequestResource` | `refund-requests` |
| `/invoice-requests` | 发票申请 | `InvoiceRequestResource` | `invoice-requests` |
| `/comment-reports` | 评论举报 | `CommentReportResource` | `comment-reports` |
| `/audit-logs` | 操作审计 | `AuditLogResource` | `audit-logs` |
| `/publish-audits` | 发布审核记录 | `PublishAuditResource` | `publish-audits` |
| `/personality-quiz` | SBTI / 人格测评配置 | **多 Resource**（维度/题目/选项/类型/设置等）+ `PersonalityQuizHubPage` 聚合入口 | 部分未写入 `menu_map`，依赖导航分区或直达 URL |
| `/admin-roles` | 角色与菜单 | `AdminRoleResource` | `admin-roles` |
| `/nav-menus` | 菜单与导航 | **主要** `AdminNavItemResource`；分区模型为 `AdminNavSectionResource`（Vue 单页可能同时管两项） | `nav-menus`（当前映射到 Item Resource） |

**无 Filament 等价路由页**

| Vue path | 说明 |
|----------|------|
| `/shared-components` | 仅前端组件展台，无后端 CRUD 对应。 |

---

## 6. Filament 有、Vue 路由表未单独列出的模块

以下 Resource 在 **Vue `router/index.js` 中没有同名 path**，但属于新版后台能力或拆分结果，验收时需单独打开：

| Resource | 说明 |
|----------|------|
| `AdminPermissionResource` | 权限字典（键级 RBAC），Vue 侧主要靠角色 + 菜单权限字符串。 |
| `AdminUserResource` | 后台用户档案（绑定 `users` 中 admin 账号），非前台用户列表。 |
| `AdminNavSectionResource` | 导航分区；Vue `nav-menus` 若合并「分区+项」，Filament 拆成两个 Resource。 |
| `UserSkinResource` | 用户与皮肤的关联/激活记录；Vue 皮肤页可能只管 `SkinConfig`。 |
| `SvipSubscriptionResource` | SVIP 内容订阅配置（关键词/来源等）；与「SVIP 定制订阅」`SvipCustomSubscriptionResource` 不同实体，勿混。 |
| `PersonalityDimensionResource` 等 | 测评题库拆表后的独立资源；入口可经 `PersonalityQuizHubPage`。 |
| `ManageSiteSettingsPage` | 自定义站点设置页；与 `SiteSettingResource` 是否重复展示，以实际侧栏为准。 |

---

## 7. `filament_admin_menu_map.php` 未映射的 Resource

未出现在映射表中的 Resource **仍可能出现在 Filament 默认发现逻辑或手动注册处**，但 **不会**从 `AdminNavRegistry` 读取侧栏标题/分组/排序，一般依赖类上的 `$navigationGroup` / `$navigationLabel` 或需在 `AdminNavItem` 中补 `menu_key` 与映射。

**已补齐（代码侧）：** `config/filament_admin_menu_map.php` 已增加 `admin-permissions`、`admin-users`、`admin-nav-sections`、`user-skins`、`svip-subscriptions` 等映射；数据库侧请执行迁移 **`2026_04_18_120000_add_admin_nav_items_filament_parity`** 写入对应 `admin_nav_items`（含 `moderation-hub` 审核工作台），执行后会 `AdminNavRegistry::forgetCache()`。

测评子 Resource 仍 **不写入** `menu_map`（侧栏已通过 `shouldRegisterNavigation=false` 隐藏），避免 `canViewAny` 被收紧为单一 `perm_key`；入口以 **`PersonalityQuizHubPage`** 为准。

**建议：** 若角色为「菜单白名单」模式，请在角色可访问菜单中为 **`moderation-hub`** 打勾（或与已有 **`moderation`** 一并授权；工作台页在代码中对二者任一通过即显示）。

---

## 8. 认证、请求与前端工程差异

| 维度 | Vue SPA 后台 | Filament |
|------|----------------|----------|
| 登录态 | 前端 axios + `/api/admin/me` 等 API，Token/Cookie 策略依项目 | Laravel Web Guard / Session（及 Filament 自带登录） |
| 页面渲染 | Vue 组件 + 自建 `AdminPageShell` | Livewire + Filament 布局 |
| 列表增强 | 如 `AdminColumnPicker`、前端筛选状态 | Filament `toggleable()`、Filters、自定义 Table Action |
| 构建 | Vite + Vue | 主要为服务端渲染 Livewire，少量 `resources/views` |

---

## 9. 列表与表单体验（持续对齐项）

| 项目 | Vue 习惯 | Filament 现状（方向） |
|------|----------|------------------------|
| 外键列 | 常显示名称、订单号、标题 | 已多处改为 `user.name`、`order.order_no`、`category.name` 等；订单 `product_id` 等仍可按需加 `formatStateUsing`。 |
| 枚举/状态 | 中文标签、Tag 颜色 | 可用 `badge()` + `formatStateUsing`；`filament_attribute_labels.php` 补字段默认中文名。 |
| 保存后跳转 | 可留在编辑页或回列表 | Resource Create/Edit 已统一 **保存后回列表**（`RedirectsToIndexAfterSave`）；自定义 Page 需单独实现。 |
| 长表单 | 分步/Tab 自定义 | 可用 `Wizard`、`Tabs` 或拆 Resource；默认多为单页 Schema。 |

---

## 10. 维护建议

- 新增后台菜单：同时维护 **`admin_nav_items`（及分区）**、**`filament_admin_menu_map.php`**、必要时 **Seeder**。
- 从 Vue 迁功能：先对照 **第 5 节路径表** 与 **第 6 节独有 Resource**，再对 **字段级** 做 diff（可用迁移 + Filament form 对照 Vue 表单）。
- 测评类：优先确认入口是 **`PersonalityQuizHubPage`** 还是各子 Resource 直链，避免运营找不到入口。

---

*文档说明：基于 `resources/js/admin/router/index.js`、`config/filament_admin_menu_map.php` 与 `app/Filament/Resources` 目录对照整理；生产环境以数据库 `admin_nav_items` / `admin_nav_sections` 为准。*

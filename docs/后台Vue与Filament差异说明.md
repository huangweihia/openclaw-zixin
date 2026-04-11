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
| `/moderation` **投稿审核** | 独立工作台式页面；Filament 侧主要由 **用户动态**（`UserPostResource`）等列表承担，无单独「审核台」聚合视图。 |
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

## 5. Vue 路由全量列表（便于查漏）

以下摘自 `resources/js/admin/router/index.js`（节选）：登录、仪表盘、投稿审核、用户与积分、订单、订阅与定制、文章与项目、分类与评论、系统设置、邮件与模板、公告、首页评价、广告位、OpenClaw 日志、会员资源、副业案例、私域 SOP、AI 工具变现、系统通知、皮肤、邮件配置、站内推送、退款/发票、评论举报、审计、发布审核、公共组件、SBTI、后台角色、菜单导航等。

**结论：** 核心业务数据多在 Filament 中有 Resource，但 **Vue 的「单页聚合、审核台、编辑器体验、组件展台」** 类定制在 Filament 中需要 **单独用 Page/Widget/自定义表单** 复刻，并非开箱一致。

## 6. 维护建议

- 新增后台能力时，同时更新 `AdminNavItem` 与 `config/filament_admin_menu_map.php`（若使用映射）。
- 需要与 Vue 完全一致的交互时，以本表为检查单做 **验收对比**（字段、默认值、保存后行为、列表列）。

---

*文档生成说明：基于仓库内 Vue 路由与 Filament Resource 列表对照整理；若生产环境关闭了部分导航，以数据库 `admin_nav_items` 为准。*

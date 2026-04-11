# 后台 Element Plus 重构说明与回滚

## 目标

- 统一使用 **Element Plus** 组件（表格、表单、对话框、分页、按钮、提示等），减少手写 `table` / `modal` / 原生控件。
- 主色与版式与现有侧栏（`#1677ff`）对齐，见 `resources/js/admin/admin-theme.css`。
- 中文语言包：`main.js` 中 `app.use(ElementPlus, { locale: zhCn })`。

## 已调整模块（本轮）

| 文件 | 说明 |
|------|------|
| `admin-theme.css` | 全局 CSS 变量、卡片圆角、表格头、分页、对话框 |
| `main.js` | 引入主题样式、`zh-cn` locale |
| `components/AdminCard.vue` | 使用 `el-card`（`shadow="hover"`） |
| `components/AdminPageShell.vue` | 副标题 `el-text`、操作区 `el-space`、内容区 `v-loading` |
| `components/AdminPagination.vue` | 使用 `el-pagination`（total、sizes、prev、pager、next、jumper） |
| `pages/Login.vue` | `el-card` + `el-form` + `el-input` + `el-button` + `ElMessage` |
| `pages/CategoriesIndex.vue` | **示例页**：`el-table` / `el-dialog` / `el-form` / `ElMessageBox` |
| `App.vue` | 主内容区最大宽度调至 `1400px` |

其余列表页仍为「壳 + 原表格 + 全局样式覆盖」，可按 `CategoriesIndex.vue` 模式逐步替换为 `el-table` + `el-dialog`。

## 本地备份（回滚）

重构前已在机器上复制整目录（**不提交 Git**，见 `.gitignore`）：

```text
resources/js/admin_backup_20260411/
```

### Windows（PowerShell）回滚示例

在项目根目录执行（先关闭正在运行的 dev server）：

```powershell
Remove-Item -Recurse -Force resources\js\admin
Copy-Item -Recurse resources\js\admin_backup_20260411 resources\js\admin
```

### Git 回滚

若已提交，可用：

```bash
git log --oneline resources/js/admin
git checkout <重构前commit> -- resources/js/admin
```

## 后续扩展清单（建议顺序）

1. 只读审计类：`AuditLogsIndex`、`PublishAuditsIndex`、`ViewHistoriesIndex`
2. 高频运营：`ArticlesIndex`、`Users`、`Orders`、`CommentsIndex`
3. 复杂弹窗页：`EmailSubscriptionsIndex`、`PushNotificationsIndex`、`SkinConfigsIndex`

每页注意：`ElMessage` 与 `axios` 响应拦截器（`main.js`）可能重复提示——若接口已返回 `message` 且会触发拦截器，页面内不必再 `ElMessage.success`。

## 构建验证

```bash
npm run build
```

确保无 Element Plus 按需引入遗漏（当前为全量 `app.use(ElementPlus)`）。

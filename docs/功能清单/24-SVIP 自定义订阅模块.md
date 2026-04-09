# OpenClaw 智信 - SVIP 自定义订阅模块

> **版本：** v4.0  
> **创建时间：** 2026-04-03  
> **模块类型：** SVIP 专属高价值功能  
> **功能总数：** 12 个

---

## 📋 一、模块总览

| 功能分类 | 功能数 | 说明 |
|---------|--------|------|
| 订阅规则管理 | 5 个 | 创建订阅、编辑订阅、删除订阅、订阅列表、订阅开关 |
| 关键词配置 | 3 个 | 关键词输入、关键词推荐、关键词黑名单 |
| 数据源配置 | 2 个 | 数据源选择、数据源优先级 |
| 推送配置 | 2 个 | 推送频率、推送方式 |

**总计：12 个功能**

---

## 📝 二、订阅规则管理（5 个功能）

### 功能 1：创建自定义订阅

| 项目 | 内容 |
|------|------|
| **功能描述** | SVIP 用户创建个性化订阅规则 |
| **页面位置** | 个人中心 /dashboard/custom-subscription |
| **UI 规格** | 表单页面，包含订阅名称、关键词、数据源、推送设置 |
| **权限控制** | 仅 SVIP 用户可见可访问 |
| **表单字段** | <table><tr><th>字段</th><th>类型</th><th>必填</th><th>说明</th></tr><tr><td>订阅名称</td><td>文本</td><td>是</td><td>例："竞品监控"、"AI 医疗动态"</td></tr><tr><td>关键词</td><td>多行文本</td><td>是</td><td>逗号分隔，例："AI 医疗，数字健康，AI 诊断"</td></tr><tr><td>数据源</td><td>多选</td><td>是</td><td>GitHub、知乎、掘金、机器之心、量子位、arXiv</td></tr><tr><td>更新频率</td><td>单选</td><td>是</td><td>每日、每周、实时</td></tr><tr><td>推送方式</td><td>多选</td><td>是</td><td>邮件、企业微信、站内通知</td></tr></table> |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**创建订阅按钮**</td><td>点击</td><td>验证表单</td><td>POST /api/custom-subscription</td></tr><tr><td>**创建订阅按钮（loading）**</td><td>点击</td><td>显示"创建中..."</td><td>禁用按钮</td></tr><tr><td>**创建成功**</td><td>-</td><td>显示成功提示</td><td>跳转到订阅列表</td></tr></table> |
| **API 调用** | POST /api/custom-subscription<br>Body: { name, keywords, sources, frequency, push_methods } |
| **数据字段** | `svip_custom_subscriptions` 表 |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 2：编辑订阅规则

| 项目 | 内容 |
|------|------|
| **功能描述** | 修改已创建的订阅规则 |
| **页面位置** | 订阅列表页操作菜单 |
| **UI 规格** | 与创建订阅相同的表单，预填充现有数据 |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**编辑按钮**</td><td>点击</td><td>打开编辑弹窗/页面</td><td>加载订阅详情</td></tr><tr><td>**保存修改**</td><td>点击</td><td>验证并保存</td><td>PUT /api/custom-subscription/{id}</td></tr><tr><td>**取消修改**</td><td>点击</td><td>关闭弹窗</td><td>不保存</td></tr></table> |
| **API 调用** | PUT /api/custom-subscription/{id} |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 3：删除订阅规则

| 项目 | 内容 |
|------|------|
| **功能描述** | 删除不再需要的订阅规则 |
| **页面位置** | 订阅列表页操作菜单 |
| **UI 规格** | 删除图标 + 文字"删除"，红色 |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**删除按钮**</td><td>点击</td><td>弹出确认框</td><td>"确定要删除此订阅吗？历史数据将保留"</td></tr><tr><td>**删除按钮（确认）**</td><td>点击</td><td>显示"删除中..."</td><td>DELETE /api/custom-subscription/{id}</td></tr><tr><td>**删除成功**</td><td>-</td><td>显示"已删除"</td><td>从列表移除</td></tr></table> |
| **API 调用** | DELETE /api/custom-subscription/{id} |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 4：订阅列表

| 项目 | 内容 |
|------|------|
| **功能描述** | 查看和管理所有自定义订阅 |
| **页面位置** | /dashboard/custom-subscription |
| **UI 规格** | 卡片列表，每项显示订阅名称、关键词、数据源、状态 |
| **列表字段** | 订阅名称、关键词预览、数据源图标、更新频率、推送方式、最后执行时间、状态开关 |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**订阅卡片**</td><td>点击</td><td>无</td><td>打开订阅详情</td></tr><tr><td>**订阅卡片**</td><td>悬停</td><td>上移 2px + 阴影加深</td><td>无</td></tr><tr><td>**操作菜单**</td><td>点击</td><td>显示编辑/删除选项</td><td>无</td></tr></table> |
| **API 调用** | GET /api/custom-subscription |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 5：订阅开关

| 项目 | 内容 |
|------|------|
| **功能描述** | 临时启用/禁用订阅规则 |
| **页面位置** | 订阅列表项 |
| **UI 规格** | 开关按钮，绿色（启用）/灰色（禁用） |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**开关按钮**</td><td>点击</td><td>切换状态</td><td>PUT /api/custom-subscription/{id}/toggle</td></tr><tr><td>**开关（启用→禁用）**</td><td>点击</td><td>变灰色</td><td>不再执行此订阅</td></tr><tr><td>**开关（禁用→启用）**</td><td>点击</td><td>变绿色</td><td>恢复执行此订阅</td></tr></table> |
| **数据字段** | `svip_custom_subscriptions.is_active` |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 🔑 三、关键词配置（3 个功能）

### 功能 6：关键词输入

| 项目 | 内容 |
|------|------|
| **功能描述** | 输入自定义订阅的关键词 |
| **页面位置** | 创建/编辑订阅表单 |
| **UI 规格** | 多行文本框，支持逗号/换行分隔 |
| **验证规则** | <table><tr><th>规则</th><th>说明</th><th>错误提示</th></tr><tr><td>必填</td><td>至少 1 个关键词</td><td>"请至少输入 1 个关键词"</td></tr><tr><td>数量</td><td>1-20 个关键词</td><td>"最多 20 个关键词"</td></tr><tr><td>长度</td><td>单关键词 2-50 字符</td><td>"关键词长度 2-50 字符"</td></tr></table> |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**输入框**</td><td>输入</td><td>实时显示关键词数量</td><td>无</td></tr><tr><td>**输入框**</td><td>失焦</td><td>验证格式</td><td>显示错误/成功</td></tr></table> |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 7：关键词推荐

| 项目 | 内容 |
|------|------|
| **功能描述** | 根据用户输入推荐相关关键词 |
| **页面位置** | 关键词输入框下方 |
| **UI 规格** | 标签式推荐列表，点击添加 |
| **推荐逻辑** | 基于热门搜索词、用户历史订阅、行业热词 |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**推荐关键词**</td><td>点击</td><td>添加到关键词列表</td><td>无</td></tr><tr><td>**推荐关键词**</td><td>悬停</td><td>背景变色</td><td>无</td></tr></table> |
| **API 调用** | GET /api/custom-subscription/keyword-suggestions?q={input} |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 8：关键词黑名单

| 项目 | 内容 |
|------|------|
| **功能描述** | 设置排除关键词，过滤不想要的内容 |
| **页面位置** | 创建/编辑订阅表单（高级选项） |
| **UI 规格** | 与关键词输入类似，标签为"排除关键词（可选）" |
| **验证规则** | 可选，格式同关键词 |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**展开/收起**</td><td>点击</td><td>展开/收起高级选项</td><td>无</td></tr><tr><td>**排除关键词**</td><td>输入</td><td>实时显示</td><td>无</td></tr></table> |
| **数据字段** | `svip_custom_subscriptions.exclude_keywords` (JSON) |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 📊 四、数据源配置（2 个功能）

### 功能 9：数据源选择

| 项目 | 内容 |
|------|------|
| **功能描述** | 选择订阅的数据来源 |
| **页面位置** | 创建/编辑订阅表单 |
| **UI 规格** | 多选卡片，每个数据源显示图标和名称 |
| **数据源列表** | <table><tr><th>数据源</th><th>图标</th><th>说明</th></tr><tr><td>GitHub</td><td>📦</td><td>GitHub Trending AI/ML 项目</td></tr><tr><td>知乎</td><td>📝</td><td>知乎 AI/副业相关问题</td></tr><tr><td>掘金</td><td>⛏️</td><td>掘金 AI 技术文章</td></tr><tr><td>机器之心</td><td>🤖</td><td>机器之心 AI 资讯</td></tr><tr><td>量子位</td><td>🔬</td><td>量子位 AI 前沿</td></tr><tr><td>arXiv</td><td>📄</td><td>arXiv 论文</td></tr></table> |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**数据源卡片**</td><td>点击</td><td>选中/取消选中</td><td>无</td></tr><tr><td>**数据源卡片**</td><td>悬停</td><td>边框变色</td><td>无</td></tr></table> |
| **数据字段** | `svip_custom_subscriptions.sources` (JSON array) |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 10：数据源优先级

| 项目 | 内容 |
|------|------|
| **功能描述** | 设置数据源的优先级（影响检索顺序） |
| **页面位置** | 数据源选择下方（高级选项） |
| **UI 规格** | 拖拽排序列表，上高下低 |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**数据源项**</td><td>拖拽</td><td>调整排序</td><td>无</td></tr><tr><td>**排序完成**</td><td>松开</td><td>保存新顺序</td><td>自动保存</td></tr></table> |
| **数据字段** | `svip_custom_submissions.source_priority` (JSON array) |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 📬 五、推送配置（2 个功能）

### 功能 11：推送频率设置

| 项目 | 内容 |
|------|------|
| **功能描述** | 设置订阅内容的推送频率 |
| **页面位置** | 创建/编辑订阅表单 |
| **UI 规格** | 单选按钮组 |
| **频率选项** | <table><tr><th>选项</th><th>说明</th><th>适用场景</th></tr><tr><td>每日</td><td>每天早上 10 点推送</td><td>高频关注</td></tr><tr><td>每周</td><td>每周一早上 10 点推送</td><td>定期回顾</td></tr><tr><td>实时</td><td>有新内容立即推送</td><td>紧急监控</td></tr></table> |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**频率选项**</td><td>点击</td><td>选中</td><td>无</td></tr></table> |
| **数据字段** | `svip_custom_subscriptions.frequency` (ENUM: daily/weekly/realtime) |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

### 功能 12：推送方式设置

| 项目 | 内容 |
|------|------|
| **功能描述** | 选择订阅内容的推送渠道 |
| **页面位置** | 创建/编辑订阅表单 |
| **UI 规格** | 多选复选框 |
| **推送方式** | <table><tr><th>方式</th><th>说明</th><th>前提条件</th></tr><tr><td>邮件</td><td>发送到绑定邮箱</td><td>已绑定邮箱</td></tr><tr><td>企业微信</td><td>发送到企业微信</td><td>已绑定企业微信</td></tr><tr><td>站内通知</td><td>网站内通知中心</td><td>无</td></tr></table> |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**推送方式**</td><td>勾选</td><td>选中/取消</td><td>无</td></tr><tr><td>**未绑定提示**</td><td>点击</td><td>显示绑定引导</td><td>跳转绑定页</td></tr></table> |
| **数据字段** | `svip_custom_subscriptions.push_methods` (JSON array) |
| **开发状态** | ⬜ 未开始 ⬜ 开发中 ⬜ 已完成 |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 🤖 六、OpenClaw 后端处理（补充说明）

### 订阅执行流程

```
1. OpenClaw 定时任务触发（每日/每周）
   ↓
2. 读取所有启用的自定义订阅规则
   ↓
3. 对每个订阅：
   - 根据关键词检索数据源
   - 去重、排序、格式化
   - 生成推送内容
   ↓
4. 根据推送方式发送
   - 邮件：调用 SMTP
   - 企业微信：调用 Webhook
   - 站内通知：写入 notifications 表
   ↓
5. 记录执行日志
```

### OpenClaw MCP 工具需求

```json
{
  "tool": "search_by_keywords",
  "description": "根据关键词检索多平台内容",
  "parameters": {
    "keywords": ["AI 医疗", "数字健康"],
    "sources": ["github", "zhihu", "juejin"],
    "limit": 20,
    "exclude_keywords": ["招聘", "广告"]
  },
  "response": {
    "items": [
      {
        "title": "...",
        "url": "...",
        "source": "github",
        "published_at": "...",
        "summary": "..."
      }
    ]
  }
}
```

---

## 📊 七、功能完成度统计

| 分类 | 功能数 | 已完成 | 已测试 | 完成率 |
|------|--------|--------|--------|--------|
| 订阅规则管理 | 5 | 0 | 0 | 0% |
| 关键词配置 | 3 | 0 | 0 | 0% |
| 数据源配置 | 2 | 0 | 0 | 0% |
| 推送配置 | 2 | 0 | 0 | 0% |
| **总计** | **12** | **0** | **0** | **0%** |

---

## 🔗 八、关联模块

| 关联模块 | 关联说明 |
|---------|---------|
| 邮件管理模块 | 邮件推送 |
| 企业微信模块 | 企业微信推送 |
| 通知中心模块 | 站内通知 |
| OpenClaw 自动化 | 定时检索执行 |

---

## 📐 九、数据库表设计

### svip_custom_subscriptions 表

```sql
CREATE TABLE svip_custom_subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    keywords JSON NOT NULL,
    exclude_keywords JSON NULL,
    sources JSON NOT NULL,
    source_priority JSON NULL,
    frequency ENUM('daily', 'weekly', 'realtime') NOT NULL,
    push_methods JSON NOT NULL,
    is_active BOOLEAN DEFAULT true,
    last_run_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### svip_subscription_logs 表（执行日志）

```sql
CREATE TABLE svip_subscription_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    executed_at TIMESTAMP,
    items_found INT DEFAULT 0,
    items_pushed INT DEFAULT 0,
    status ENUM('success', 'partial', 'failed'),
    error_message TEXT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES svip_custom_subscriptions(id)
);
```

---

_文档版本：v4.0_  
_最后更新：2026-04-03_  
_维护者：OpenClaw 智信开发团队_

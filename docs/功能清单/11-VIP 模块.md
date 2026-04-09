# OpenClaw 智信 - VIP 模块功能清单

> **模块：** VIP 模块  
> **页面路径：** /vip, /max/pricing  
> **功能数：** 4 个  
> **最后更新：** 2026-04-05

---

## 功能 1：VIP 权益页展示

| 项目 | 内容 |
|------|------|
| **功能描述** | 展示 VIP 会员权益 |
| **页面位置** | /vip |
| **涉及数据库** | 表：subscriptions<br>字段：subscriptions.plan, subscriptions.amount |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**权益卡片**</td><td>悬停</td><td>卡片上移 + 阴影加深</td><td>无</td></tr><tr><td>**开通 VIP**</td><td>点击</td><td>无</td><td>跳转价格页 (/max/pricing)</td></tr></table> |
| **数据逻辑** | 1. 查询 subscriptions 表的套餐配置<br>2. 显示所有 VIP 权益列表 |
| **开发状态** | ✅ 已完成（`resources/views/vip/index.blade.php`，`layouts.site` + 换肤类） |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 功能 2：价格方案页展示

| 项目 | 内容 |
|------|------|
| **功能描述** | 展示价格方案对比 |
| **页面位置** | /max/pricing |
| **涉及数据库** | 表：subscriptions<br>字段：subscriptions.plan, subscriptions.amount |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**价格卡片**</td><td>悬停</td><td>卡片上移 + 阴影加深</td><td>无</td></tr><tr><td>**免费方案**</td><td>点击</td><td>无</td><td>无（纯展示）</td></tr><tr><td>**VIP 方案**</td><td>点击</td><td>无</td><td>跳转 **`/payments/confirm?plan=vip`** → 登录后可 **创建订单** → **`/payments/result`**（演示支付见 `12-支付模块.md`）</td></tr><tr><td>**SVIP 方案**</td><td>点击</td><td>无</td><td>同上 `plan=svip`</td></tr></table><br>**UI：** 「选择此方案」等为 **卡片内居中、非通栏** 主按钮（`vip-plan-cta`），避免视觉上过长。 |
| **数据逻辑** | 1. 查询 subscriptions 表的所有套餐<br>2. 显示 plan、amount、权益对比 |
| **开发状态** | ✅ 已完成（`vip/pricing.blade.php`，锚点 `#plan-vip` 等） |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 功能 3：会员状态展示

| 项目 | 内容 |
|------|------|
| **功能描述** | 展示当前会员状态 |
| **页面位置** | /dashboard |
| **涉及数据库** | 表：subscriptions, users<br>字段：subscriptions.plan, subscriptions.status, subscriptions.expires_at, users.role |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**当前套餐**</td><td>-</td><td>显示套餐信息</td><td>无</td></tr><tr><td>**到期时间**</td><td>-</td><td>显示剩余天数</td><td>无</td></tr><tr><td>**续费**</td><td>点击</td><td>无</td><td>跳转支付页</td></tr><tr><td>**升级套餐**</td><td>点击</td><td>无</td><td>跳转价格页</td></tr></table> |
| **数据逻辑** | 1. 查询当前用户的订阅记录<br>2. 显示 plan、status、expires_at<br>3. 计算剩余天数 |
| **开发状态** | 🟡 部分完成（`/dashboard` 展示身份、到期日；无独立订阅订单表） |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

## 功能 4：VIP 标识展示

| 项目 | 内容 |
|------|------|
| **功能描述** | 在页面显示 VIP 标识 |
| **页面位置** | 所有页面（导航栏、用户头像旁） |
| **涉及数据库** | 表：users<br>字段：users.role |
| **交互详情** | <table><tr><th>元素</th><th>点击/悬停</th><th>反应</th><th>跳转/动作</th></tr><tr><td>**VIP 标识**</td><td>-</td><td>显示👑图标</td><td>无</td></tr><tr><td>**VIP 标识**</td><td>悬停</td><td>显示到期时间</td><td>无</td></tr></table> |
| **数据逻辑** | 1. 检查 users.role 是否为'vip'或'svip'<br>2. 检查 subscription_ends_at 是否过期<br>3. 显示对应标识 |
| **开发状态** | 🟡 部分完成（顶栏下拉会员区；头像旁独立角标可后续加） |
| **测试状态** | ⬜ 未测试 ⬜ 测试中 ⬜ 测试通过 |

---

_文档版本：v4.0_  
_最后更新：2026-04-06_  
_维护者：OpenClaw 智信开发团队_

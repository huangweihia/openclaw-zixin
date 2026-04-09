<script setup>
import { ref } from 'vue';
import SkinSwitcher from '../Components/SkinSwitcher.vue';
import { toast } from '../frontend/utils/toast';

const isLoading = ref(false);
const isSaving = ref(false);
const loadingData = ref(true);

// 测试 Toast
function testToast(type) {
    const messages = {
        success: '操作成功！数据已保存',
        error: '操作失败！请重试',
        warning: '警告：数据未保存',
        info: '提示：系统正在维护',
    };
    toast[type](messages[type]);
}

// 测试按钮 loading
function testSubmit() {
    isLoading.value = true;
    setTimeout(() => {
        isLoading.value = false;
        toast.success('提交成功！');
    }, 2000);
}

function testSave() {
    isSaving.value = true;
    setTimeout(() => {
        isSaving.value = false;
        toast.success('保存成功！');
    }, 3000);
}

// 模拟数据加载
setTimeout(() => {
    loadingData.value = false;
}, 3000);
</script>

<template>
    <div class="test-page">
        <header class="test-top-nav">
            <router-link to="/" class="test-top-nav__back">← 首页</router-link>
            <span class="test-top-nav__title">组件测试</span>
            <SkinSwitcher variant="navbar" />
        </header>
        <h1 class="test-title">🧪 组件测试页面</h1>
        
        <!-- Toast 测试 -->
        <section class="test-section">
            <h2>1️⃣ Toast 提示测试</h2>
            <div class="test-buttons">
                <button @click="testToast('success')" class="btn btn-success">
                    ✅ 成功提示
                </button>
                <button @click="testToast('error')" class="btn btn-error">
                    ❌ 失败提示
                </button>
                <button @click="testToast('warning')" class="btn btn-warning">
                    ⚠️ 警告提示
                </button>
                <button @click="testToast('info')" class="btn btn-info">
                    ℹ️ 信息提示
                </button>
            </div>
            <p class="test-desc">点击按钮查看右上角的 Toast 提示</p>
        </section>
        
        <!-- 按钮 Loading 测试 -->
        <section class="test-section">
            <h2>2️⃣ 按钮 Loading 测试</h2>
            <div class="test-buttons">
                <button 
                    v-loading="isLoading" 
                    @click="testSubmit"
                    class="btn btn-primary"
                >
                    提交（普通 loading）
                </button>
                <button 
                    v-loading:full="isSaving" 
                    @click="testSave"
                    class="btn btn-primary"
                >
                    保存（完整 loading）
                </button>
            </div>
            <p class="test-desc">点击按钮查看 loading 状态（按钮禁用 + 旋转图标）</p>
        </section>
        
        <!-- 骨架屏测试 -->
        <section class="test-section">
            <h2>3️⃣ 骨架屏测试</h2>
            
            <div class="test-grid">
                <!-- 文本骨架 -->
                <div class="test-card">
                    <h3>文本骨架</h3>
                    <Skeleton type="text" :rows="3" />
                </div>
                
                <!-- 图片骨架 -->
                <div class="test-card">
                    <h3>图片骨架</h3>
                    <Skeleton type="image" height="150px" />
                </div>
                
                <!-- 卡片骨架 -->
                <div class="test-card">
                    <h3>卡片骨架</h3>
                    <Skeleton type="card" />
                </div>
                
                <!-- 列表骨架 -->
                <div class="test-card full">
                    <h3>列表骨架</h3>
                    <Skeleton type="list" :rows="3" />
                </div>
            </div>
            
            <p class="test-desc">骨架屏会显示闪烁动画（1.5 秒循环）</p>
        </section>
        
        <!-- 实际使用场景 -->
        <section class="test-section">
            <h2>4️⃣ 实际使用场景</h2>
            
            <div class="test-card full">
                <h3>数据加载中...</h3>
                <div v-if="loadingData">
                    <Skeleton type="list" :rows="5" />
                </div>
                <div v-else class="content-loaded">
                    <div class="list-item">
                        <span class="avatar">👤</span>
                        <div class="item-content">
                            <h4>用户 1</h4>
                            <p>这是一条测试数据</p>
                        </div>
                    </div>
                    <div class="list-item">
                        <span class="avatar">👤</span>
                        <div class="item-content">
                            <h4>用户 2</h4>
                            <p>这是另一条测试数据</p>
                        </div>
                    </div>
                    <div class="list-item">
                        <span class="avatar">👤</span>
                        <div class="item-content">
                            <h4>用户 3</h4>
                            <p>还有一条测试数据</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="test-desc">3 秒后自动加载完成，显示实际内容</p>
        </section>
    </div>
</template>

<style scoped>
.test-top-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin: -40px -20px 28px;
    padding: 12px 20px;
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}
.test-top-nav__back {
    font-size: 0.9rem;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
}
.test-top-nav__back:hover {
    color: var(--primary, #6366f1);
}
.test-top-nav__title {
    flex: 1;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #334155;
}
.test-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.test-title {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 40px;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.test-section {
    margin-bottom: 40px;
    padding: 24px;
    background: var(--bg-secondary, #ffffff);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.test-section h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary, #1e293b);
}

.test-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: var(--gradient-primary);
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-error {
    background: #ef4444;
    color: white;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-info {
    background: #3b82f6;
    color: white;
}

.test-desc {
    color: var(--text-secondary, #64748b);
    font-size: 14px;
    margin-top: 12px;
}

.test-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.test-card {
    padding: 16px;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
}

.test-card.full {
    grid-column: 1 / -1;
}

.test-card h3 {
    font-size: 18px;
    margin-bottom: 16px;
    color: var(--text-primary, #1e293b);
}

.content-loaded {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.list-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    border-radius: 8px;
    background: var(--bg-primary, #f8fafc);
}

.avatar {
    font-size: 32px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-primary);
    border-radius: 50%;
}

.item-content h4 {
    margin: 0 0 4px 0;
    color: var(--text-primary, #1e293b);
}

.item-content p {
    margin: 0;
    color: var(--text-secondary, #64748b);
    font-size: 14px;
}
</style>

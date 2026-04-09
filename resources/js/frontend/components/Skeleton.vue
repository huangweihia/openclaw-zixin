<script setup>
const props = defineProps({
    type: {
        type: String,
        default: 'text', // text, image, card, list
    },
    rows: {
        type: Number,
        default: 3,
    },
    width: {
        type: String,
        default: '100%',
    },
    height: {
        type: String,
        default: '20px',
    },
});
</script>

<template>
    <div class="skeleton-container" :style="{ width }">
        <!-- 图片骨架 -->
        <div v-if="type === 'image'" class="skeleton-image" :style="{ height }"></div>
        
        <!-- 文本骨架 -->
        <template v-if="type === 'text' || type === 'card' || type === 'list'">
            <div 
                v-for="i in rows" 
                :key="i" 
                class="skeleton-text"
                :style="{ 
                    height,
                    width: i === rows && type === 'text' ? '60%' : '100%',
                    marginBottom: i < rows ? '8px' : '0'
                }"
            ></div>
        </template>
        
        <!-- 卡片骨架 -->
        <div v-if="type === 'card'" class="skeleton-card">
            <div class="skeleton-card-image"></div>
            <div class="skeleton-card-content">
                <div class="skeleton-card-title"></div>
                <div class="skeleton-card-text"></div>
                <div class="skeleton-card-text short"></div>
            </div>
        </div>
        
        <!-- 列表骨架 -->
        <div v-if="type === 'list'" class="skeleton-list">
            <div v-for="i in rows" :key="i" class="skeleton-list-item">
                <div class="skeleton-list-avatar"></div>
                <div class="skeleton-list-content">
                    <div class="skeleton-list-title"></div>
                    <div class="skeleton-list-text"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.skeleton-container {
    display: inline-block;
}

/* 基础骨架样式 */
.skeleton-text,
.skeleton-image {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

.skeleton-image {
    width: 100%;
}

/* 卡片骨架 */
.skeleton-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.skeleton-card-image {
    height: 200px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

.skeleton-card-content {
    padding: 16px;
}

.skeleton-card-title {
    height: 24px;
    width: 80%;
    margin-bottom: 12px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

.skeleton-card-text {
    height: 16px;
    margin-bottom: 8px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

.skeleton-card-text.short {
    width: 60%;
}

/* 列表骨架 */
.skeleton-list-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.skeleton-list-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    flex-shrink: 0;
}

.skeleton-list-content {
    flex: 1;
}

.skeleton-list-title {
    height: 18px;
    width: 70%;
    margin-bottom: 8px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

.skeleton-list-text {
    height: 14px;
    width: 90%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

/* 闪烁动画 */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
</style>

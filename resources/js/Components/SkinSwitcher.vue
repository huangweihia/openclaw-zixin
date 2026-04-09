<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    variant: {
        type: String,
        default: 'navbar', // 'navbar' or 'floating'
    }
});

const emit = defineEmits(['skin-change']);

const skins = ref([]);
const currentSkin = ref(null);
const showPanel = ref(false);
const loading = ref(false);

const panelPosition = computed(() => {
    return props.variant === 'navbar' ? 'top-right' : 'bottom-right';
});

// 加载皮肤列表
async function loadSkins() {
    try {
        const { data } = await axios.get('/api/skins');
        if (data.success) {
            skins.value = data.data || [];
            console.log('皮肤列表:', skins.value);
            
            // 确保 css_variables 是对象
            skins.value.forEach(skin => {
                if (typeof skin.css_variables === 'string') {
                    try {
                        skin.css_variables = JSON.parse(skin.css_variables);
                    } catch (e) {
                        console.error('解析 CSS 变量失败:', e);
                        skin.css_variables = {};
                    }
                }
                // 兼容后台可能保存的 "--key" 形式，统一去掉前缀避免重复 "--"
                if (skin.css_variables && typeof skin.css_variables === 'object') {
                    const normalized = {};
                    Object.entries(skin.css_variables).forEach(([k, v]) => {
                        const nk = String(k).replace(/^--+/, '');
                        normalized[nk] = v;
                    });
                    skin.css_variables = normalized;
                }
            });
        }
    } catch (error) {
        console.error('加载皮肤列表失败:', error);
    }
}

// 加载当前用户皮肤（已登录：以接口为准并写入 localStorage；未登录：仅预览默认色，不覆盖 localStorage，留给 restoreSkin）
async function loadCurrentSkin() {
    const isLoggedIn = !!document.querySelector('meta[name="user-id"]');
    const persistLocal = isLoggedIn;
    // 已登录：若本地已选过皮肤，优先使用本地，避免接口兜底覆盖导致「点首页又变回去」
    if (isLoggedIn) {
        const savedSkin = localStorage.getItem('preferred_skin');
        if (savedSkin && skins.value.find((s) => s.code === savedSkin)) {
            currentSkin.value = skins.value.find((s) => s.code === savedSkin) || null;
            applySkin(savedSkin, { persistLocal: true });

            return;
        }
    }
    try {
        const { data } = await axios.get('/api/skins/current');
        if (data.success && data.data?.current_skin) {
            const current = data.data.current_skin;
            // 私有皮肤在列表接口漏出时，强制合并到列表，确保可见可切换
            if (current?.code && !skins.value.find((s) => s.code === current.code)) {
                skins.value.unshift(current);
            }
            currentSkin.value = current;
            applySkin(data.data.current_skin.code, { persistLocal });
        } else {
            if (skins.value.length > 0) {
                currentSkin.value = skins.value[0];
                applySkin(skins.value[0].code, { persistLocal });
            }
        }
    } catch (error) {
        console.error('加载当前皮肤失败:', error);
        if (!isLoggedIn) {
            restoreSkin();
        }
    }
}

// 获取皮肤预览颜色
function getSkinPreview(skin) {
    if (!skin.css_variables) {
        console.warn('皮肤没有 css_variables:', skin.name);
        return 'linear-gradient(135deg, #6366f1, #ec4899)';
    }
    
    const vars = skin.css_variables;
    console.log(`皮肤 ${skin.name} 的完整配置:`, JSON.stringify(vars, null, 2));
    
    // 检查所有可能的字段名
    const gradientKey = vars['gradient-primary'] ? 'gradient-primary' : 
                       vars['gradient_primary'] ? 'gradient_primary' : null;
    
    // 优先使用 gradient-primary
    if (gradientKey && vars[gradientKey]) {
        console.log(`✅ 使用 ${gradientKey}:`, vars[gradientKey]);
        return vars[gradientKey];
    }
    
    // 其次使用 primary + secondary 渐变
    if (vars.primary && vars.secondary) {
        console.log(`✅ 使用 primary+secondary 渐变: ${vars.primary} → ${vars.secondary}`);
        return `linear-gradient(135deg, ${vars.primary}, ${vars.secondary})`;
    }
    
    // 只有 primary 时使用 primary 纯色
    if (vars.primary) {
        console.log(`✅ 使用 primary 纯色:`, vars.primary);
        return vars.primary;
    }
    
    // 默认颜色
    console.log('❌ 无可用颜色，使用默认');
    return 'linear-gradient(135deg, #6366f1, #ec4899)';
}

// 应用皮肤（persistLocal：是否同步到 localStorage；服务端拉取未登录预览时应为 false，避免覆盖用户本地选择）
function applySkin(skinCode, options = {}) {
    const persistLocal = options.persistLocal !== false;
    document.documentElement.setAttribute('data-skin', skinCode);
    if (persistLocal) {
        localStorage.setItem('preferred_skin', skinCode);
    }
    
    // 应用 CSS 变量
    const skin = skins.value.find(s => s.code === skinCode);
    if (skin && skin.css_variables) {
        const root = document.documentElement;
        const vars = skin.css_variables;

        Object.entries(vars).forEach(([key, value]) => {
            if (value == null || String(value).trim() === '') {
                return;
            }
            root.style.setProperty(`--${key}`, String(value).trim());
        });

        // 未配置 gradient-primary 时再用 primary/secondary 兜底（兼容旧数据或直写 JSON）
        const hasGradient =
            (vars['gradient-primary'] && String(vars['gradient-primary']).trim() !== '') ||
            (vars.gradient_primary && String(vars.gradient_primary).trim() !== '');
        if (!hasGradient) {
            if (vars.primary && vars.secondary) {
                root.style.setProperty(
                    '--gradient-primary',
                    `linear-gradient(135deg, ${vars.primary}, ${vars.secondary})`,
                );
            } else if (vars.primary) {
                root.style.setProperty(
                    '--gradient-primary',
                    `linear-gradient(135deg, ${vars.primary}, ${vars.primary})`,
                );
            }
        }
    }
    
    emit('skin-change', skinCode);
}

// 切换皮肤
async function changeSkin(skinCode) {
    const skin = skins.value.find(s => s.code === skinCode);
    const isLoggedIn = !!document.querySelector('meta[name="user-id"]');
    const role = (document.querySelector('meta[name="user-role"]')?.getAttribute('content') || '').toLowerCase();
    const skinType = String(skin?.type || '').toLowerCase();

    if (skin && (skinType === 'vip' || skinType === 'svip')) {
        if (!isLoggedIn) {
            const ret = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.href = `/login?return=${ret}`;
            return;
        }
        const isAdmin = role === 'admin';
        const isVip = role === 'vip' || role === 'svip' || isAdmin;
        const isSvip = role === 'svip' || isAdmin;
        if (skinType === 'vip' && !isVip) {
            window.location.href = '/max/pricing';
            return;
        }
        if (skinType === 'svip' && !isSvip) {
            window.location.href = '/max/pricing';
            return;
        }
    }
    
    loading.value = true;
    try {
        // 未登录时只保存到 localStorage，不调用 API
        if (!isLoggedIn) {
            // 未登录，直接保存到本地
            currentSkin.value = skin;
            applySkin(skinCode);
            showPanel.value = false;
            showToast(`已切换到 ${skin.name}`);
            loading.value = false;
            return;
        }
        
        // 已登录，保存到数据库
        try {
            await axios.put('/api/skins/current', { skin_code: skinCode });
        } catch (e) {
            console.log('保存失败，仅使用本地存储');
        }
        
        currentSkin.value = skin;
        applySkin(skinCode);
        showPanel.value = false;
        
        showToast(`已切换到 ${skin.name}`);
    } catch (error) {
        console.error('切换皮肤失败:', error);
        showToast('切换失败，请重试');
    } finally {
        loading.value = false;
    }
}

// 切换面板显示
function togglePanel() {
    showPanel.value = !showPanel.value;
}

// 关闭面板
function closePanel() {
    showPanel.value = false;
}

// 显示提示
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'skin-toast';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.9);
        color: #fff;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 9999;
        animation: fadeInUp 0.3s ease;
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOutDown 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// 点击外部关闭面板
function handleClickOutside(event) {
    if (showPanel.value && !event.target.closest('.skin-switcher')) {
        closePanel();
    }
}

// 页面加载时恢复皮肤
function restoreSkin() {
    const savedSkin = localStorage.getItem('preferred_skin');
    if (savedSkin) {
        const skin = skins.value.find(s => s.code === savedSkin);
        if (skin) {
            currentSkin.value = skin;
            applySkin(savedSkin);
        }
    }
}

onMounted(async () => {
    await loadSkins();
    await loadCurrentSkin();
    // 未登录：在 loadCurrentSkin 未写入 localStorage 的前提下，用本地缓存覆盖默认预览
    if (!document.querySelector('meta[name="user-id"]')) {
        restoreSkin();
    }
    document.addEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="skin-switcher" :class="`skin-switcher--${variant}`">
        <!-- 切换按钮 -->
        <button 
            class="skin-switcher__btn" 
            @click="togglePanel"
            title="切换皮肤"
            type="button"
        >
            <span class="skin-switcher__icon">🎨</span>
        </button>

        <!-- 皮肤选择面板 -->
        <transition name="slide-fade">
            <div v-if="showPanel" class="skin-panel" :class="`skin-panel--${panelPosition}`">
                <div class="skin-panel__header">
                    <h3 class="skin-panel__title">选择皮肤</h3>
                    <button class="skin-panel__close" @click="closePanel" type="button">×</button>
                </div>
                
                <div class="skin-panel__list">
                    <div 
                        v-for="skin in skins" 
                        :key="skin.code"
                        class="skin-option"
                        :class="{ 'is-active': currentSkin?.code === skin.code }"
                        @click="changeSkin(skin.code)"
                    >
                        <div 
                            class="skin-option__preview" 
                            :style="{ background: getSkinPreview(skin) }"
                        ></div>
                        <div class="skin-option__info">
                            <span class="skin-option__name">{{ skin.name }}</span>
                            <span v-if="String(skin.type || '').toLowerCase() === 'vip'" class="skin-option__badge vip">VIP</span>
                            <span v-if="String(skin.type || '').toLowerCase() === 'svip'" class="skin-option__badge svip">SVIP</span>
                        </div>
                    </div>
                </div>
                
                <div v-if="loading" class="skin-panel__loading">
                    <span>切换中...</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.skin-switcher {
    position: relative;
    display: inline-block;
}

.skin-switcher__btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 20px;
}

.skin-switcher__btn:hover {
    background: rgba(255, 255, 255, 1);
    border-color: var(--primary, #6366f1);
    transform: scale(1.05);
}

.skin-panel {
    position: absolute;
    z-index: 1000;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(148, 163, 184, 0.15);
    min-width: 280px;
    overflow: hidden;
}

.skin-panel--top-right {
    top: 100%;
    right: 0;
    margin-top: 8px;
}

.skin-panel--bottom-right {
    bottom: 100%;
    right: 0;
    margin-bottom: 8px;
}

.skin-panel__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(236, 72, 153, 0.05));
}

.skin-panel__title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.skin-panel__close {
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    font-size: 18px;
    color: #64748b;
    cursor: pointer;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.skin-panel__close:hover {
    background: rgba(148, 163, 184, 0.1);
    color: #1e293b;
}

.skin-panel__list {
    padding: 8px;
    max-height: 320px;
    overflow-y: auto;
}

.skin-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}

.skin-option:hover {
    background: rgba(99, 102, 241, 0.05);
}

.skin-option.is-active {
    background: rgba(99, 102, 241, 0.08);
    border-color: var(--primary, #6366f1);
}

.skin-option__preview {
    width: 48px;
    height: 32px;
    border-radius: 6px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.skin-option__info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.skin-option__name {
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    white-space: nowrap;
}

.skin-option__badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.skin-option__badge.vip {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}
.skin-option__badge.svip {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: #fff;
}

.skin-panel__loading {
    padding: 16px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

/* 动画 */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.2s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

@keyframes fadeOutDown {
    from {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    to {
        opacity: 0;
        transform: translateX(-50%) translateY(20px);
    }
}
</style>

/**
 * 按钮 Loading 指令
 * 使用方式：
 * <button v-loading="isLoading">提交</button>
 * <button v-loading:full="isLoading">提交</button>
 */

export default {
    mounted(el, binding) {
        const isLoading = binding.value;
        const modifiers = binding.modifiers;
        
        if (isLoading) {
            showLoading(el, modifiers);
        }
    },
    updated(el, binding) {
        const isLoading = binding.value;
        const oldValue = binding.oldValue;
        const modifiers = binding.modifiers
        
        if (isLoading !== oldValue) {
            if (isLoading) {
                showLoading(el, modifiers);
            } else {
                hideLoading(el);
            }
        }
    }
};

function showLoading(el, modifiers) {
    // 禁用按钮
    el.disabled = true;
    el.classList.add('opacity-75', 'cursor-not-allowed');
    
    // 保存原始内容
    if (!el.dataset.originalContent) {
        el.dataset.originalContent = el.innerHTML;
    }
    
    // 创建 loading 内容
    const loadingContent = modifiers.full 
        ? '<span class="flex items-center justify-center gap-2"><span class="loading-spinner"></span> 处理中...</span>'
        : '<span class="loading-spinner"></span>';
    
    el.innerHTML = loadingContent;
    
    // 添加样式
    el.classList.add('relative');
}

function hideLoading(el) {
    // 启用按钮
    el.disabled = false;
    el.classList.remove('opacity-75', 'cursor-not-allowed', 'relative');
    
    // 恢复原始内容
    if (el.dataset.originalContent) {
        el.innerHTML = el.dataset.originalContent;
        delete el.dataset.originalContent;
    }
}

// 添加全局样式
const style = document.createElement('style');
style.textContent = `
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

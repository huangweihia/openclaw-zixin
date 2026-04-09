/**
 * Toast 通知工具
 * 使用方式：
 * toast.success('操作成功')
 * toast.error('操作失败')
 * toast.warning('警告信息')
 * toast.info('提示信息')
 */

let toastContainer = null;

// 初始化 Toast 容器
function initToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

// 创建 Toast 元素
function createToast(message, type = 'info', duration = 3000) {
    const container = initToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg border-l-4 transition-all duration-300 transform translate-x-full opacity-0`;
    
    // 类型样式
    const styles = {
        success: { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-800' },
        error: { bg: 'bg-red-50', border: 'border-red-500', text: 'text-red-800' },
        warning: { bg: 'bg-orange-50', border: 'border-orange-500', text: 'text-orange-800' },
        info: { bg: 'bg-blue-50', border: 'border-blue-500', text: 'text-blue-800' },
    };
    
    const style = styles[type] || styles.info;
    toast.classList.add(style.bg, style.border);
    
    // 图标
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️',
    };
    
    toast.innerHTML = `
        <span class="text-2xl">${icons[type] || icons.info}</span>
        <span class="font-medium ${style.text}">${message}</span>
        <button
            class="ml-2 inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-300/70 bg-white/85 text-slate-500 hover:bg-white hover:text-slate-700 hover:border-slate-400 transition"
            aria-label="关闭通知"
            title="关闭"
            type="button"
            onclick="this.closest('.toast').remove()"
        >×</button>
    `;
    
    container.appendChild(toast);
    
    // 显示动画
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    });
    
    // 自动关闭
    if (duration > 0) {
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    return toast;
}

// 导出 API
export const toast = {
    success: (message, duration) => createToast(message, 'success', duration),
    error: (message, duration) => createToast(message, 'error', duration),
    warning: (message, duration) => createToast(message, 'warning', duration),
    info: (message, duration) => createToast(message, 'info', duration),
};

// 自动初始化
if (typeof window !== 'undefined') {
    window.toast = toast;
}

import * as ElementPlusIconsVue from '@element-plus/icons-vue';

/**
 * 菜单 icon 存库格式：ep:Odometer（与 @element-plus/icons-vue 导出同名）
 */
export function resolveAdminNavIcon(iconRef) {
    if (!iconRef || typeof iconRef !== 'string') return null;
    if (!iconRef.startsWith('ep:')) return null;
    const name = iconRef.slice(3);
    return ElementPlusIconsVue[name] ?? null;
}

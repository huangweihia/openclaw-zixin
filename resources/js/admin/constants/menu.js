/**
 * 侧边栏数据由 GET /api/admin/nav-menu 提供；此处仅保留路由匹配工具函数。
 */
export function isMenuItemActive(routePath, item) {
    if (!item) return false;
    if (item.external_url) return false;
    const to = item.to;
    if (String(to || '').startsWith('__ext:')) return false;
    if (item.match === 'exact') {
        return routePath === '/' || routePath === '';
    }
    if (to === '/' || to === '') {
        return routePath === '/' || routePath === '';
    }
    return routePath === to || routePath.startsWith(`${to}/`);
}

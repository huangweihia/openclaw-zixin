let currentUser = null;

export function setAdminUser(user) {
    currentUser = user || null;
}

export function getAdminUser() {
    return currentUser;
}

export function getAdminPermissions() {
    if (!currentUser || !Array.isArray(currentUser.admin_permissions)) {
        return [];
    }
    return currentUser.admin_permissions;
}

export function can(permissionKey) {
    const perms = getAdminPermissions();
    if (!permissionKey) {
        return false;
    }

    if (perms.includes('*')) {
        return true;
    }

    return perms.includes(permissionKey);
}


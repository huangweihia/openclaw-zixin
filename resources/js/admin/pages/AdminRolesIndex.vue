<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';
import { can } from '../permissions';

const rows = ref([]);
const err = ref('');
const dialogOpen = ref(false);
const editingId = ref(null);
const isSuperEdit = ref(false);
const menuCatalog = ref([]);
const allPermissions = ref([]);
const form = ref({
    name: '',
    key: '',
    description: '',
    menu_mode: 'inherit',
    permission_ids: [],
    menu_keys: [],
});

const canWrite = computed(() => can('admin:roles:write'));

async function loadList() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/admin-roles');
        rows.value = data.roles ?? [];
    } catch {
        err.value = '加载失败';
    }
}

async function loadFormOptions() {
    const { data } = await axios.get('/api/admin/admin-roles/form-options');
    menuCatalog.value = data.menu_catalog ?? [];
    allPermissions.value = data.all_permissions ?? [];
}

onMounted(async () => {
    await loadList();
});

async function openCreate() {
    err.value = '';
    editingId.value = null;
    isSuperEdit.value = false;
    await loadFormOptions();
    form.value = {
        name: '',
        key: '',
        description: '',
        menu_mode: 'inherit',
        permission_ids: [],
        menu_keys: [],
    };
    dialogOpen.value = true;
}

async function openEdit(row) {
    err.value = '';
    editingId.value = row.id;
    isSuperEdit.value = row.key === 'super-admin';
    try {
        const { data } = await axios.get(`/api/admin/admin-roles/${row.id}`);
        menuCatalog.value = data.menu_catalog ?? [];
        allPermissions.value = data.all_permissions ?? [];
        const r = data.role;
        form.value = {
            name: r.name,
            key: r.key,
            description: r.description || '',
            menu_mode: r.menu_mode || 'inherit',
            permission_ids: r.permission_ids || [],
            menu_keys: r.menu_keys || [],
        };
        dialogOpen.value = true;
    } catch {
        err.value = '加载角色失败';
    }
}

function closeDialog() {
    dialogOpen.value = false;
    err.value = '';
}

function toggleMenuKey(key, checked) {
    const set = new Set(form.value.menu_keys);
    if (checked) {
        set.add(key);
    } else {
        set.delete(key);
    }
    form.value.menu_keys = [...set];
}

function isMenuKeyChecked(key) {
    return form.value.menu_keys.includes(key);
}

async function save() {
    err.value = '';
    const payload = {
        name: form.value.name,
        key: form.value.key,
        description: form.value.description || null,
        menu_mode: form.value.menu_mode,
        permission_ids: form.value.permission_ids,
        menu_keys: form.value.menu_mode === 'whitelist' ? form.value.menu_keys : [],
    };
    try {
        if (editingId.value == null) {
            await axios.post('/api/admin/admin-roles', payload);
            ElMessage.success('已创建');
        } else if (isSuperEdit.value) {
            await axios.put(`/api/admin/admin-roles/${editingId.value}`, {
                name: form.value.name,
                description: form.value.description || null,
            });
            ElMessage.success('已更新');
        } else {
            await axios.put(`/api/admin/admin-roles/${editingId.value}`, payload);
            ElMessage.success('已更新');
        }
        closeDialog();
        await loadList();
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}

async function removeRow(row) {
    if (row.key === 'super-admin') return;
    try {
        await ElMessageBox.confirm(`确定删除角色「${row.name}」？已分配给用户的角色将解除关联。`, '删除角色', {
            type: 'warning',
            confirmButtonText: '删除',
            cancelButtonText: '取消',
        });
    } catch {
        return;
    }
    try {
        await axios.delete(`/api/admin/admin-roles/${row.id}`);
        ElMessage.success('已删除');
        await loadList();
    } catch (e) {
        ElMessage.error(e.response?.data?.message || '删除失败');
    }
}

const permLabel = (p) => `${p.key}${p.description ? ` — ${p.description}` : ''}`;
</script>

<template>
    <AdminPageShell title="角色与菜单" lead="按角色分配接口权限；可选「菜单白名单」收窄侧边栏可见项（仍受权限约束）。">
        <template #actions>
            <el-button v-if="canWrite" type="primary" @click="openCreate">新建角色</el-button>
        </template>

        <el-alert v-if="err && !dialogOpen" type="error" :closable="false" show-icon class="oc-roles-alert" :title="err" />

        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无角色">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column prop="name" label="名称" min-width="140" show-overflow-tooltip />
                <el-table-column prop="key" label="标识" min-width="120">
                    <template #default="{ row }">
                        <span class="mono">{{ row.key }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="菜单模式" width="120">
                    <template #default="{ row }">
                        <el-tag size="small" :type="row.menu_mode === 'whitelist' ? 'warning' : 'info'">
                            {{ row.menu_mode === 'whitelist' ? '白名单' : '继承权限' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="menu_items_count" label="菜单项数" width="100" />
                <el-table-column label="操作" width="200" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openEdit(row)">编辑</el-button>
                        <el-button v-if="canWrite && row.key !== 'super-admin'" link type="danger" @click="removeRow(row)">
                            删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </AdminCard>

        <el-dialog
            v-model="dialogOpen"
            :title="editingId == null ? '新建角色' : isSuperEdit ? '编辑超级管理员（仅名称/说明）' : '编辑角色'"
            width="640px"
            destroy-on-close
            align-center
            @closed="closeDialog"
        >
            <el-alert v-if="err" type="error" :closable="false" show-icon class="mb-3" :title="err" />
            <el-form label-position="top">
                <el-form-item label="名称" required>
                    <el-input v-model="form.name" placeholder="展示名称" clearable />
                </el-form-item>
                <el-form-item v-if="editingId == null" label="标识 key" required>
                    <el-input v-model="form.key" placeholder="小写字母、数字、连字符，如 content-editor" clearable />
                </el-form-item>
                <el-form-item v-else-if="!isSuperEdit" label="标识 key">
                    <el-input v-model="form.key" disabled class="mono" />
                </el-form-item>
                <el-form-item label="说明">
                    <el-input v-model="form.description" type="textarea" :rows="2" placeholder="可选" />
                </el-form-item>

                <template v-if="!isSuperEdit">
                    <el-form-item label="接口权限">
                        <el-select
                            v-model="form.permission_ids"
                            multiple
                            filterable
                            collapse-tags
                            collapse-tags-tooltip
                            placeholder="选择权限点"
                            class="w-full"
                        >
                            <el-option
                                v-for="p in allPermissions"
                                :key="p.id"
                                :label="permLabel(p)"
                                :value="p.id"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="侧边栏菜单">
                        <el-radio-group v-model="form.menu_mode">
                            <el-radio-button value="inherit">与权限一致（不额外收窄）</el-radio-button>
                            <el-radio-button value="whitelist">白名单（仅勾选项且须有对应读权限）</el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                    <div v-if="form.menu_mode === 'whitelist'" class="menu-pick">
                        <div v-for="block in menuCatalog" :key="block.section" class="menu-pick__block">
                            <div class="menu-pick__title">{{ block.section }}</div>
                            <div class="menu-pick__row">
                                <el-checkbox
                                    v-for="it in block.items"
                                    :key="it.key"
                                    :model-value="isMenuKeyChecked(it.key)"
                                    @update:model-value="(v) => toggleMenuKey(it.key, v)"
                                >
                                    {{ it.label }}
                                </el-checkbox>
                            </div>
                        </div>
                    </div>
                </template>
            </el-form>
            <template #footer>
                <el-button @click="closeDialog">取消</el-button>
                <el-button v-if="canWrite" type="primary" @click="save">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-roles-alert {
    margin-bottom: 12px;
}
.mb-3 {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
.w-full {
    width: 100%;
}
.menu-pick {
    max-height: 320px;
    overflow: auto;
    padding: 8px 0;
    border-top: 1px solid var(--el-border-color-lighter);
}
.menu-pick__block {
    margin-bottom: 12px;
}
.menu-pick__title {
    font-size: 12px;
    font-weight: 700;
    color: var(--el-text-color-secondary);
    margin-bottom: 6px;
}
.menu-pick__row {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 12px;
}
</style>

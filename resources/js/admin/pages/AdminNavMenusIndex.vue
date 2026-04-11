<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';
import { can } from '../permissions';

const sections = ref([]);
const err = ref('');
const sectionDialog = ref(false);
const itemDialog = ref(false);
const sectionForm = ref({ title: '', sort_order: 0, is_active: true });
const itemForm = ref({
    id: null,
    admin_nav_section_id: null,
    menu_key: '',
    label: '',
    path: '',
    external_url: '',
    icon: '',
    perm_key: '',
    sort_order: 0,
    match_exact: false,
    is_active: true,
});

const canWrite = computed(() => can('admin:menus:write'));

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/nav-config');
        sections.value = data.sections ?? [];
    } catch {
        err.value = '加载失败';
    }
}

onMounted(load);

function openNewSection() {
    sectionForm.value = { title: '', sort_order: 0, is_active: true };
    sectionDialog.value = true;
}

async function saveSectionMeta() {
    try {
        await axios.post('/api/admin/nav-config/sections', sectionForm.value);
        sectionDialog.value = false;
        await load();
    } catch {
        /* axios 已提示 */
    }
}

async function toggleSectionActive(sec) {
    if (!canWrite.value) return;
    try {
        await axios.put(`/api/admin/nav-config/sections/${sec.id}`, {
            title: sec.title,
            sort_order: sec.sort_order,
            is_active: sec.is_active,
        });
        await load();
    } catch {
        await load();
    }
}

async function removeSection(sec) {
    try {
        await ElMessageBox.confirm(`删除分组「${sec.title}」将同时删除其下所有菜单项。`, '确认删除', {
            type: 'warning',
        });
    } catch {
        return;
    }
    try {
        await axios.delete(`/api/admin/nav-config/sections/${sec.id}`);
        await load();
    } catch {
        /* */
    }
}

function openNewItem(sec) {
    itemForm.value = {
        id: null,
        admin_nav_section_id: sec.id,
        menu_key: '',
        label: '',
        path: '',
        external_url: '',
        icon: 'ep:Document',
        perm_key: 'admin:dashboard:read',
        sort_order: 0,
        match_exact: false,
        is_active: true,
    };
    itemDialog.value = true;
}

function openEditItem(row, secId) {
    itemForm.value = {
        id: row.id,
        admin_nav_section_id: secId,
        menu_key: row.menu_key,
        label: row.label,
        path: row.path || '',
        external_url: row.external_url || '',
        icon: row.icon || '',
        perm_key: row.perm_key,
        sort_order: row.sort_order ?? 0,
        match_exact: !!row.match_exact,
        is_active: !!row.is_active,
    };
    itemDialog.value = true;
}

function closeItemDialog() {
    itemDialog.value = false;
}

async function saveItem() {
    const payload = { ...itemForm.value };
    delete payload.id;
    try {
        if (itemForm.value.id == null) {
            await axios.post('/api/admin/nav-config/items', payload);
        } else {
            await axios.put(`/api/admin/nav-config/items/${itemForm.value.id}`, payload);
        }
        closeItemDialog();
        await load();
    } catch {
        /* */
    }
}

async function removeItem(row) {
    try {
        await ElMessageBox.confirm(`删除菜单项「${row.label}」？`, '确认', { type: 'warning' });
    } catch {
        return;
    }
    try {
        await axios.delete(`/api/admin/nav-config/items/${row.id}`);
        await load();
    } catch {
        /* */
    }
}
</script>

<template>
    <AdminPageShell
        title="菜单与导航"
        lead="侧边栏分组与菜单项存库；排序用数字，图标可用 ep:Odometer 等形式（见 Element Plus Icons）。站内路径需已在路由注册；外链填完整 URL。"
    >
        <template #actions>
            <el-button v-if="canWrite" type="primary" @click="openNewSection">新建分组</el-button>
            <el-button @click="load">刷新</el-button>
        </template>

        <el-alert v-if="err" type="error" :closable="false" show-icon class="mb-3" :title="err" />

        <div class="nav-stack">
            <AdminCard v-for="sec in sections" :key="sec.id" class="nav-sec-card">
                <div class="nav-sec-head">
                    <div class="nav-sec-title">
                        <span class="nav-sec-name">{{ sec.title }}</span>
                        <el-tag size="small" type="info">sort {{ sec.sort_order }}</el-tag>
                    </div>
                    <div class="nav-sec-actions">
                        <span class="nav-sec-label">启用</span>
                        <el-switch
                            :model-value="sec.is_active"
                            :disabled="!canWrite"
                            @change="(v) => { sec.is_active = v; toggleSectionActive(sec); }"
                        />
                        <el-button v-if="canWrite" type="primary" link @click="openNewItem(sec)">新增项</el-button>
                        <el-button v-if="canWrite" type="danger" link @click="removeSection(sec)">删分组</el-button>
                    </div>
                </div>
                <el-table :data="sec.items || []" stripe border size="small" empty-text="暂无菜单项">
                    <el-table-column prop="menu_key" label="Key" width="160" show-overflow-tooltip>
                        <template #default="{ row }">
                            <span class="mono">{{ row.menu_key }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="label" label="名称" min-width="120" />
                    <el-table-column prop="path" label="路径" min-width="140" show-overflow-tooltip />
                    <el-table-column prop="external_url" label="外链" min-width="120" show-overflow-tooltip />
                    <el-table-column prop="perm_key" label="权限" min-width="160" show-overflow-tooltip />
                    <el-table-column prop="icon" label="图标" width="120" show-overflow-tooltip />
                    <el-table-column label="精匹配" width="88">
                        <template #default="{ row }">
                            <el-tag :type="row.match_exact ? 'warning' : 'info'" size="small">
                                {{ row.match_exact ? '是' : '否' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="sort_order" label="排序" width="72" />
                    <el-table-column label="操作" width="140" fixed="right">
                        <template #default="{ row }">
                            <el-button link type="primary" @click="openEditItem(row, sec.id)">编辑</el-button>
                            <el-button v-if="canWrite" link type="danger" @click="removeItem(row)">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </AdminCard>
        </div>

        <el-dialog v-model="sectionDialog" title="新建分组" width="440px" destroy-on-close align-center>
            <el-form label-position="top">
                <el-form-item label="标题" required>
                    <el-input v-model="sectionForm.title" placeholder="如：运营工具" />
                </el-form-item>
                <el-form-item label="排序（越小越靠前）">
                    <el-input-number v-model="sectionForm.sort_order" :min="0" :step="10" controls-position="right" class="w-full" />
                </el-form-item>
                <el-form-item label="">
                    <el-checkbox v-model="sectionForm.is_active">启用</el-checkbox>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="sectionDialog = false">取消</el-button>
                <el-button type="primary" :disabled="!canWrite" @click="saveSectionMeta">保存</el-button>
            </template>
        </el-dialog>

        <el-dialog
            v-model="itemDialog"
            :title="itemForm.id == null ? '新建菜单项' : '编辑菜单项'"
            width="560px"
            destroy-on-close
            align-center
            @closed="closeItemDialog"
        >
            <el-form label-position="top">
                <el-form-item v-if="itemForm.id == null" label="菜单 key（唯一）" required>
                    <el-input v-model="itemForm.menu_key" placeholder="如：my-report" class="mono" />
                </el-form-item>
                <el-form-item v-else label="菜单 key">
                    <el-input v-model="itemForm.menu_key" disabled class="mono" />
                </el-form-item>
                <el-form-item label="显示名称" required>
                    <el-input v-model="itemForm.label" />
                </el-form-item>
                <el-form-item label="站内路径">
                    <el-input v-model="itemForm.path" placeholder="/my-page ；与外链二选一或只填路径" class="mono" />
                </el-form-item>
                <el-form-item label="外链 URL">
                    <el-input v-model="itemForm.external_url" placeholder="https://..." />
                </el-form-item>
                <el-form-item label="权限键 perm_key" required>
                    <el-input v-model="itemForm.perm_key" placeholder="admin:xxx:read" class="mono" />
                </el-form-item>
                <el-form-item label="图标">
                    <el-input v-model="itemForm.icon" placeholder="ep:Document 或 emoji" />
                </el-form-item>
                <el-form-item label="排序">
                    <el-input-number v-model="itemForm.sort_order" :min="0" :step="10" controls-position="right" class="w-full" />
                </el-form-item>
                <el-form-item label="">
                    <el-checkbox v-model="itemForm.match_exact">路径精匹配（用于仪表盘 /）</el-checkbox>
                </el-form-item>
                <el-form-item label="">
                    <el-checkbox v-model="itemForm.is_active">启用</el-checkbox>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="closeItemDialog">取消</el-button>
                <el-button v-if="canWrite" type="primary" @click="saveItem">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.mb-3 {
    margin-bottom: 12px;
}
.nav-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.nav-sec-card {
    padding: 0;
}
.nav-sec-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 12px;
}
.nav-sec-title {
    display: flex;
    align-items: center;
    gap: 8px;
}
.nav-sec-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--el-text-color-primary);
}
.nav-sec-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.nav-sec-label {
    font-size: 12px;
    color: var(--el-text-color-secondary);
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
.w-full {
    width: 100%;
}
</style>

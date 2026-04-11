<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const err = ref('');
const editing = ref(null);
const form = ref({ name: '', slug: '', description: '', sort: 0, is_premium: false });

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/categories');
        rows.value = data.categories ?? [];
    } catch {
        err.value = '加载失败';
    }
}

onMounted(load);

function openCreate() {
    err.value = '';
    editing.value = 'new';
    form.value = { name: '', slug: '', description: '', sort: 0, is_premium: false };
}

function openEdit(c) {
    err.value = '';
    editing.value = c.id;
    form.value = {
        name: c.name,
        slug: c.slug,
        description: c.description || '',
        sort: c.sort ?? 0,
        is_premium: !!c.is_premium,
    };
}

function closeModal() {
    editing.value = null;
    err.value = '';
}

async function save() {
    err.value = '';
    const { slug: _slug, ...payload } = form.value;
    try {
        if (editing.value === 'new') {
            await axios.post('/api/admin/categories', payload);
            ElMessage.success('已创建');
        } else {
            await axios.put(`/api/admin/categories/${editing.value}`, payload);
            ElMessage.success('已更新');
        }
        closeModal();
        await load();
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}

async function removeRow(id) {
    try {
        await ElMessageBox.confirm('确定删除？关联文章/项目分类将置空。', '删除分类', {
            type: 'warning',
            confirmButtonText: '删除',
            cancelButtonText: '取消',
        });
    } catch {
        return;
    }
    try {
        await axios.delete(`/api/admin/categories/${id}`);
        ElMessage.success('已删除');
        await load();
    } catch {
        err.value = '删除失败';
        ElMessage.error('删除失败');
    }
}
</script>

<template>
    <AdminPageShell title="分类管理" lead="用于文章/项目等内容的分类维护。">
        <template #actions>
            <el-button type="primary" @click="openCreate">新建分类</el-button>
        </template>

        <el-alert v-if="err && editing === null" type="error" :closable="false" show-icon class="oc-cat-alert" :title="err" />

        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无分类">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column prop="name" label="名称" min-width="140" show-overflow-tooltip />
                <el-table-column prop="slug" label="短标识" min-width="120">
                    <template #default="{ row }">
                        <span class="mono">{{ row.slug }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序" width="88" />
                <el-table-column label="付费" width="88">
                    <template #default="{ row }">
                        <el-tag :type="row.is_premium ? 'warning' : 'info'" size="small">
                            {{ row.is_premium ? '是' : '否' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="160" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="removeRow(row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </AdminCard>

        <el-dialog
            :model-value="editing !== null"
            :title="editing === 'new' ? '新建分类' : '编辑分类'"
            width="520px"
            destroy-on-close
            align-center
            @update:model-value="(v) => !v && closeModal()"
        >
            <el-alert v-if="err && editing !== null" type="error" :closable="false" show-icon class="mb-3" :title="err" />
            <el-form label-position="top">
                <el-form-item label="名称" required>
                    <el-input v-model="form.name" placeholder="分类名称" clearable />
                </el-form-item>
                <el-form-item v-if="editing === 'new'" label="短标识">
                    <el-text type="info" size="small">保存后由系统根据名称自动生成（用于 URL），创建后不可改。</el-text>
                </el-form-item>
                <el-form-item v-else label="短标识">
                    <el-input v-model="form.slug" disabled class="mono" />
                    <el-text type="info" size="small" class="mt-1 block">仅展示，不可修改。</el-text>
                </el-form-item>
                <el-form-item label="描述">
                    <el-input v-model="form.description" placeholder="可选" clearable />
                </el-form-item>
                <el-form-item label="排序（越大越靠前）">
                    <el-input-number v-model="form.sort" :min="0" :step="1" controls-position="right" class="w-full" />
                </el-form-item>
                <el-form-item label="">
                    <el-checkbox v-model="form.is_premium">付费分类</el-checkbox>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="closeModal">取消</el-button>
                <el-button type="primary" @click="save">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-cat-alert {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
.mb-3 {
    margin-bottom: 12px;
}
.mt-1 {
    margin-top: 4px;
}
.block {
    display: block;
}
.w-full {
    width: 100%;
}
</style>

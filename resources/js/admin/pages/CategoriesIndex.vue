<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const err = ref('');
const msg = ref('');
const editing = ref(null);
const form = ref({ name: '', slug: '', description: '', sort: 0, is_premium: false }); // slug 仅编辑展示

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
    // slug 新建不填，由后端生成
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
    msg.value = '';
    err.value = '';
    const { slug: _slug, ...payload } = form.value;
    try {
        if (editing.value === 'new') {
            await axios.post('/api/admin/categories', payload);
            msg.value = '已创建';
        } else {
            await axios.put(`/api/admin/categories/${editing.value}`, payload);
            msg.value = '已更新';
        }
        closeModal();
        await load();
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}

async function removeRow(id) {
    if (!confirm('确定删除？关联文章/项目分类将置空。')) {
        return;
    }
    try {
        await axios.delete(`/api/admin/categories/${id}`);
        await load();
    } catch {
        err.value = '删除失败';
    }
}
</script>

<template>
    <AdminPageShell title="分类管理" lead="用于文章/项目等内容的分类维护。">
        <template #actions>
            <button type="button" class="btn primary" @click="openCreate">新建分类</button>
        </template>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !editing" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>短标识</th>
                        <th>排序</th>
                        <th>付费</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in rows" :key="c.id">
                        <td>{{ c.id }}</td>
                        <td>{{ c.name }}</td>
                        <td class="mono">{{ c.slug }}</td>
                        <td>{{ c.sort }}</td>
                        <td>{{ c.is_premium ? '是' : '否' }}</td>
                        <td class="actions">
                            <button type="button" class="link" @click="openEdit(c)">编辑</button>
                            <button type="button" class="link danger" @click="removeRow(c.id)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无分类</p>
        </AdminCard>

        <div v-if="editing !== null" class="modal" @click.self="closeModal">
            <div class="modal__box" @click.stop>
                <h2>{{ editing === 'new' ? '新建分类' : '编辑分类' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="field">
                    <span>名称 *</span>
                    <input v-model="form.name" type="text" />
                </label>
                <div v-if="editing === 'new'" class="field field--note">
                    <span>短标识</span>
                    <span class="field-hint">保存后由系统根据名称自动生成（用于 URL），创建后不可改。</span>
                </div>
                <label v-else class="field">
                    <span>短标识</span>
                    <span class="field-hint">仅展示，不可修改。</span>
                    <p class="slug-readonly mono">{{ form.slug }}</p>
                </label>
                <label class="field">
                    <span>描述</span>
                    <input v-model="form.description" type="text" />
                </label>
                <label class="field">
                    <span>排序（越大越靠前）</span>
                    <input v-model.number="form.sort" type="number" />
                </label>
                <label class="check">
                    <input v-model="form.is_premium" type="checkbox" />
                    付费分类
                </label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeModal">取消</button>
                    <button type="button" class="btn primary" @click="save">保存</button>
                </div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.table th,
.table td {
    padding: 0.6rem 0.85rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.8rem;
}
.actions {
    display: flex;
    gap: 0.5rem;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
}
.link.danger {
    color: #b91c1c;
}
.empty {
    padding: 1.25rem;
    color: #64748b;
    margin: 0;
}
.btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 100;
}
.modal__box {
    background: #fff;
    border-radius: 10px;
    padding: 1.25rem;
    width: 100%;
    max-width: 420px;
}
.modal__box h2 {
    margin: 0 0 1rem;
    font-size: 1.1rem;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
    font-size: 0.88rem;
}
.field input {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.88rem;
}
.modal__btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
</style>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { enumOptions } from '../constants/labels';
import AdminPageShell from '../components/AdminPageShell.vue';

const projectDifficultyOpts = enumOptions('projectDifficulty');

const route = useRoute();
const router = useRouter();
const isCreate = computed(() => route.name === 'project-create');

const categories = ref([]);
const err = ref('');
const msg = ref('');

const emptyForm = () => ({
    name: '',
    full_name: '',
    description: '',
    url: '',
    language: '',
    stars: 0,
    forks: 0,
    score: 0,
    tagsText: '',
    monetization: '',
    difficulty: 'medium',
    is_featured: false,
    is_vip: false,
    category_id: null,
});

const form = ref(emptyForm());

async function loadCategories() {
    const { data } = await axios.get('/api/admin/categories');
    categories.value = data.categories ?? [];
}

async function loadProject() {
    if (isCreate.value) {
        form.value = emptyForm();
        return;
    }
    const { data } = await axios.get(`/api/admin/projects/${route.params.id}`);
    const p = data.project;
    form.value = {
        name: p.name,
        full_name: p.full_name || '',
        description: p.description || '',
        url: p.url,
        language: p.language || '',
        stars: p.stars ?? 0,
        forks: p.forks ?? 0,
        score: p.score ?? 0,
        tagsText: Array.isArray(p.tags) ? p.tags.join(', ') : '',
        monetization: p.monetization || '',
        difficulty: p.difficulty || 'medium',
        is_featured: !!p.is_featured,
        is_vip: !!p.is_vip,
        category_id: p.category_id,
    };
}

watch(
    () => route.fullPath,
    async () => {
        err.value = '';
        try {
            await loadCategories();
            await loadProject();
        } catch {
            err.value = '加载失败';
        }
    },
    { immediate: true }
);

async function save() {
    err.value = '';
    msg.value = '';
    const tags = form.value.tagsText
        .split(',')
        .map((s) => s.trim())
        .filter(Boolean);
    const payload = {
        name: form.value.name,
        full_name: form.value.full_name || null,
        description: form.value.description || null,
        url: form.value.url,
        language: form.value.language || null,
        stars: form.value.stars,
        forks: form.value.forks,
        score: form.value.score,
        tags,
        monetization: form.value.monetization || null,
        difficulty: form.value.difficulty,
        is_featured: form.value.is_featured,
        is_vip: form.value.is_vip,
        category_id: form.value.category_id || null,
    };
    try {
        if (isCreate.value) {
            const { data } = await axios.post('/api/admin/projects', payload);
            msg.value = data.message;
            router.replace({ name: 'project-edit', params: { id: String(data.project.id) } });
        } else {
            const { data } = await axios.put(`/api/admin/projects/${route.params.id}`, payload);
            msg.value = data.message;
        }
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}
</script>

<template>
    <AdminPageShell :title="isCreate ? '新建项目' : '编辑项目'">
        <template #actions>
            <button type="button" class="link" @click="router.push({ name: 'projects' })">← 返回列表</button>
        </template>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err" class="err">{{ err }}</p>
        <div class="card">
            <label class="field">
                <span>名称 *</span>
                <input v-model="form.name" type="text" />
            </label>
            <label class="field">
                <span>完整名（user/repo）</span>
                <input v-model="form.full_name" type="text" />
            </label>
            <label class="field">
                <span>GitHub URL *</span>
                <input v-model="form.url" type="text" />
            </label>
            <label class="field">
                <span>描述</span>
                <textarea v-model="form.description" rows="5" />
            </label>
            <label class="field">
                <span>语言</span>
                <input v-model="form.language" type="text" />
            </label>
            <label class="field">
                <span>Stars / Forks</span>
                <div class="row2">
                    <input v-model.number="form.stars" type="number" min="0" />
                    <input v-model.number="form.forks" type="number" min="0" />
                </div>
            </label>
            <label class="field">
                <span>评分</span>
                <input v-model.number="form.score" type="number" step="0.01" min="0" />
            </label>
            <label class="field">
                <span>标签（逗号分隔）</span>
                <input v-model="form.tagsText" type="text" />
            </label>
            <label class="field">
                <span>变现分析</span>
                <textarea v-model="form.monetization" rows="3" />
            </label>
            <label class="field">
                <span>难度</span>
                <select v-model="form.difficulty">
                    <option v-for="o in projectDifficultyOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>
            </label>
            <label class="field">
                <span>分类</span>
                <select v-model.number="form.category_id">
                    <option :value="null">—</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </label>
            <label class="check">
                <input v-model="form.is_featured" type="checkbox" />
                推荐
            </label>
            <label class="check">
                <input v-model="form.is_vip" type="checkbox" />
                VIP 专属
            </label>
            <button type="button" class="btn primary" @click="save">保存</button>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.back {
    margin: 0 0 0.5rem;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
}
.page-title {
    margin: 0 0 1rem;
    font-size: 1.5rem;
}
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.card {
    background: #fff;
    padding: 1.25rem;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
    max-width: 720px;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.85rem;
    font-size: 0.88rem;
}
.field span {
    color: #64748b;
}
.field input,
.field select,
.field textarea {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.row2 {
    display: flex;
    gap: 0.5rem;
}
.row2 input {
    flex: 1;
}
.check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
    font-size: 0.88rem;
}
.btn {
    margin-top: 0.5rem;
    padding: 0.55rem 1.2rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}
.btn.primary {
    background: #2563eb;
    color: #fff;
}
</style>

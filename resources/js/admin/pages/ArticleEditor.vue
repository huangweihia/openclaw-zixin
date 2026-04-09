<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const isCreate = computed(() => route.name === 'article-create');

const categories = ref([]);
const err = ref('');
const msg = ref('');

const emptyForm = () => ({
    category_id: null,
    title: '',
    slug: '',
    summary: '',
    content: '',
    cover_image: '',
    author_id: null,
    is_vip: false,
    is_published: false,
    published_at: '',
    source_url: '',
    meta_keywords: '',
    meta_description: '',
});

const form = ref(emptyForm());

async function loadCategories() {
    const { data } = await axios.get('/api/admin/categories');
    categories.value = data.categories ?? [];
}

async function loadArticle() {
    if (isCreate.value) {
        form.value = emptyForm();
        return;
    }
    const { data } = await axios.get(`/api/admin/articles/${route.params.id}`);
    const a = data.article;
    form.value = {
        category_id: a.category_id,
        title: a.title,
        slug: a.slug,
        summary: a.summary || '',
        content: a.content || '',
        cover_image: a.cover_image || '',
        author_id: a.author_id,
        is_vip: !!a.is_vip,
        is_published: !!a.is_published,
        published_at: a.published_at ? a.published_at.slice(0, 16) : '',
        source_url: a.source_url || '',
        meta_keywords: a.meta_keywords || '',
        meta_description: a.meta_description || '',
    };
}

watch(
    () => route.fullPath,
    async () => {
        err.value = '';
        try {
            await loadCategories();
            await loadArticle();
        } catch {
            err.value = '加载失败';
        }
    },
    { immediate: true }
);

async function save() {
    err.value = '';
    msg.value = '';
    const payload = { ...form.value };
    delete payload.slug;
    if (!payload.published_at) {
        payload.published_at = null;
    } else {
        payload.published_at = new Date(payload.published_at).toISOString();
    }
    if (!payload.category_id) {
        payload.category_id = null;
    }
    if (!Number.isFinite(Number(payload.author_id)) || Number(payload.author_id) < 1) {
        payload.author_id = null;
    } else {
        payload.author_id = Number(payload.author_id);
    }
    try {
        if (isCreate.value) {
            const { data } = await axios.post('/api/admin/articles', payload);
            msg.value = data.message;
            router.replace({ name: 'article-edit', params: { id: String(data.article.id) } });
        } else {
            const { data } = await axios.put(`/api/admin/articles/${route.params.id}`, payload);
            msg.value = data.message;
        }
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}
</script>

<template>
    <div>
        <p class="back">
            <button type="button" class="link" @click="router.push({ name: 'articles' })">← 返回列表</button>
        </p>
        <h1 class="page-title">{{ isCreate ? '新建文章' : '编辑文章' }}</h1>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err" class="err">{{ err }}</p>
        <div class="card">
            <label class="field">
                <span>分类</span>
                <select v-model.number="form.category_id">
                    <option :value="null">—</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </label>
            <label class="field">
                <span>标题 *</span>
                <input v-model="form.title" type="text" />
            </label>
            <div v-if="isCreate" class="field field--note">
                <span>URL 别名（slug）</span>
                <span class="field-hint">保存后由系统根据标题自动生成。</span>
            </div>
            <label v-else class="field">
                <span>URL 别名（slug）</span>
                <p class="field-readonly mono">{{ form.slug }}</p>
            </label>
            <label class="field">
                <span>摘要</span>
                <input v-model="form.summary" type="text" />
            </label>
            <label class="field">
                <span>正文 HTML</span>
                <textarea v-model="form.content" rows="14" />
            </label>
            <label class="field">
                <span>封面图 URL</span>
                <input v-model="form.cover_image" type="text" />
            </label>
            <label class="field">
                <span>作者用户 ID</span>
                <input v-model.number="form.author_id" type="number" min="1" />
            </label>
            <label class="check">
                <input v-model="form.is_vip" type="checkbox" />
                VIP 专属
            </label>
            <label class="check">
                <input v-model="form.is_published" type="checkbox" />
                已发布
            </label>
            <label class="field">
                <span>发布时间</span>
                <input v-model="form.published_at" type="datetime-local" />
            </label>
            <label class="field">
                <span>来源 URL</span>
                <input v-model="form.source_url" type="text" />
            </label>
            <label class="field">
                <span>meta keywords</span>
                <input v-model="form.meta_keywords" type="text" />
            </label>
            <label class="field">
                <span>meta description</span>
                <input v-model="form.meta_description" type="text" />
            </label>
            <button type="button" class="btn primary" @click="save">保存</button>
        </div>
    </div>
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
.field--note {
    margin-bottom: 0.85rem;
    font-size: 0.88rem;
}
.field-hint {
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.45;
}
.field-readonly {
    margin: 0;
    padding: 0.45rem 0.55rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.95rem;
    color: #0f172a;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.88rem;
}
.field input,
.field select,
.field textarea {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.95rem;
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

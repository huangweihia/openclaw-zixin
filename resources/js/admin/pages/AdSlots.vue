<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';

const adSlotTypeOpts = enumOptions('adSlotType');
const adAudienceOpts = enumOptions('adAudience');
const positionOpts = [
    { value: 'top', label: '顶部' },
    { value: 'bottom', label: '底部' },
    { value: 'left', label: '左侧' },
    { value: 'right', label: '右侧' },
];

const rows = ref([]);
const perPage = ref(20);
const query = ref('');
const meta = ref({ current_page: 1, last_page: 1 });
const err = ref('');
const saveErr = ref('');
const msg = ref('');
const showCreate = ref(false);
const editing = ref(null);
const fallbackFileInput = ref(null);
const fallbackUploading = ref(false);
const fallbackUploadErr = ref('');
const fallbackImgBust = ref(0);
const fallbackImgLoadError = ref(false);

const emptyForm = () => ({
    name: '',
    position: 'top',
    type: 'banner',
    width: null,
    height: null,
    sort: 0,
    default_title: '',
    default_image_url: '',
    default_link_url: '',
    default_content: '',
    show_default_when_empty: true,
    audience: 'all',
});

const form = ref(emptyForm());

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/ad-slots', { params: { page, per_page: perPage.value, q: query.value || undefined } });
        rows.value = data.data ?? [];
        meta.value = { current_page: data.current_page || 1, last_page: data.last_page || 1 };
    } catch {
        err.value = '加载失败';
    }
}

onMounted(() => load(1));
watch([perPage, query], () => load(1));

function openCreate() {
    saveErr.value = '';
    editing.value = null;
    form.value = emptyForm();
    showCreate.value = true;
}

function openEdit(s) {
    saveErr.value = '';
    showCreate.value = false;
    editing.value = s;
    form.value = {
        name: s.name,
        code: s.code,
        position: s.position,
        type: s.type,
        width: s.width,
        height: s.height,
        sort: s.sort ?? 0,
        audience: s.audience || 'all',
        default_title: s.default_title || '',
        default_image_url: s.default_image_url || '',
        default_link_url: s.default_link_url || '',
        default_content: s.default_content || '',
        show_default_when_empty: s.show_default_when_empty !== false,
    };
}

function closeModal() {
    showCreate.value = false;
    editing.value = null;
    saveErr.value = '';
    fallbackUploadErr.value = '';
}

function openFallbackFilePicker() {
    fallbackFileInput.value?.click();
}

function fallbackImageSrc() {
    const u = (form.value.default_image_url || '').trim();
    if (!u) return '';
    const sep = u.includes('?') ? '&' : '?';
    return `${u}${sep}v=${fallbackImgBust.value}`;
}

watch(
    () => form.value.default_image_url,
    () => {
        fallbackImgLoadError.value = false;
    },
);

async function onFallbackImageChange(ev) {
    const input = ev.target;
    const file = input.files?.[0];
    if (!file) return;
    fallbackUploadErr.value = '';
    fallbackUploading.value = true;
    try {
        const fd = new FormData();
        fd.append('image', file);
        const { data } = await axios.post('/api/admin/uploads/image', fd);
        if (data.url) {
            form.value.default_image_url = data.url;
            fallbackImgBust.value = Date.now();
            fallbackImgLoadError.value = false;
        }
    } catch {
        fallbackUploadErr.value = '上传失败（请使用 jpg/png/gif/webp，单张不超过 5MB）';
    } finally {
        fallbackUploading.value = false;
        input.value = '';
    }
}

async function toggle(s) {
    err.value = '';
    try {
        await axios.post(`/api/admin/ad-slots/${s.id}/toggle`);
        await load(meta.value.current_page || 1);
    } catch {
        err.value = '操作失败';
    }
}

async function createSlot() {
    saveErr.value = '';
    try {
        const { code: _omit, ...payload } = form.value;
        await axios.post('/api/admin/ad-slots', payload);
        msg.value = '已创建';
        closeModal();
        await load(1);
    } catch {
        saveErr.value = '创建失败';
    }
}

async function saveEdit() {
    if (!editing.value) return;
    saveErr.value = '';
    try {
        const { code: _omit, ...payload } = form.value;
        await axios.put(`/api/admin/ad-slots/${editing.value.id}`, payload);
        msg.value = '已保存';
        closeModal();
        await load(meta.value.current_page || 1);
    } catch {
        saveErr.value = '保存失败';
    }
}
</script>

<template>
    <div>
        <div class="head">
            <h1 class="page-title">广告位</h1>
            <div class="head__actions">
                <input v-model="query" type="search" class="search" placeholder="搜索名称/代码" />
                <select v-model="perPage" class="per-page">
                    <option :value="10">10 / 页</option>
                    <option :value="20">20 / 页</option>
                    <option :value="50">50 / 页</option>
                    <option :value="100">100 / 页</option>
                </select>
                <button type="button" class="btn primary" @click="openCreate">新建广告位</button>
            </div>
        </div>
        <p class="lead">
            当前仅保留<strong>广告位兜底素材</strong>（图片/链接/文案），不再使用广告投放。系统层面已限制为<strong>全局同时仅可启用 1 个广告位</strong>。
        </p>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !showCreate && !editing" class="err">{{ err }}</p>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>代码</th>
                        <th>名称</th>
                        <th>兜底图</th>
                        <th>位置</th>
                        <th>可见人群</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in rows" :key="s.id">
                        <td class="mono">{{ s.code }}</td>
                        <td>{{ s.name }}</td>
                        <td class="muted">{{ s.default_image_url ? '已设' : '—' }}</td>
                        <td>{{ positionOpts.find((p) => p.value === s.position)?.label || s.position }}</td>
                        <td>{{ enumLabel('adAudience', s.audience || 'all') }}</td>
                        <td>{{ s.is_active ? '启用' : '禁用' }}</td>
                        <td class="acts">
                            <button type="button" class="link" @click="openEdit(s)">编辑</button>
                            <button type="button" class="link" @click="toggle(s)">切换</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无广告位</p>
        </div>
        <AdminPagination
            v-if="meta.last_page > 1"
            :meta="meta"
            @go="load"
        />

        <div v-if="showCreate || editing" class="modal" @click.self="closeModal">
            <div class="modal__box modal__box--lg" @click.stop>
                <h2>{{ editing ? `编辑：${editing.code}` : '新建广告位' }}</h2>
                <p v-if="saveErr" class="admin-modal-err">{{ saveErr }}</p>

                <h3 class="sub">版位信息</h3>
                <label class="field">
                    <span>名称</span>
                    <input v-model="form.name" type="text" />
                </label>
                <div v-if="showCreate" class="field field--note">
                    <span>唯一代码</span>
                    <span class="field-tip">保存后由系统根据名称自动生成，用于程序引用广告位；创建后不可改。</span>
                </div>
                <label v-else class="field">
                    <span>唯一代码</span>
                    <span class="field-tip">程序引用标识，不可修改。</span>
                    <p class="field-code mono">{{ form.code }}</p>
                </label>
                <label class="field">
                    <span>展示位置</span>
                    <select v-model="form.position">
                        <option v-for="p in positionOpts" :key="p.value" :value="p.value">{{ p.label }}</option>
                    </select>
                    <span class="field-tip">
                        与前台一致：<strong>顶部/底部</strong>仅对代码为
                        <code class="inline-code">home-banner</code>
                        的通栏位生效；<strong>左侧/右侧</strong>会在首页与内页侧栏展示，且<strong>不会</strong>再占顶部通栏。竖版素材请用侧栏位并配合宽高。
                    </span>
                </label>
                <label class="field">
                    <span>类型</span>
                    <select v-model="form.type">
                        <option v-for="o in adSlotTypeOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>可见人群</span>
                    <select v-model="form.audience">
                        <option v-for="o in adAudienceOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                    <span class="field-tip">用于前台权限控制；例如可设置仅游客、仅会员、仅登录用户可见。</span>
                </label>
                <div class="row2">
                    <label class="field">
                        <span>宽 px</span>
                        <input v-model.number="form.width" type="number" />
                    </label>
                    <label class="field">
                        <span>高 px</span>
                        <input v-model.number="form.height" type="number" />
                    </label>
                </div>
                <label class="field">
                    <span>排序（大优先）</span>
                    <input v-model.number="form.sort" type="number" />
                </label>

                <h3 class="sub">兜底素材（无投放或占位用）</h3>
                <label class="check">
                    <input v-model="form.show_default_when_empty" type="checkbox" />
                    无有效投放时允许使用兜底素材
                </label>
                <p class="field-hint">
                    只要填写了兜底标题、图片、跳转或自定义内容<strong>任意一项</strong>，前台在无有效投放时即会展示兜底；跳转链接选填（例如仅展示二维码图）。下方勾选仅作运营备忘，不影响是否展示。
                </p>
                <label class="field">
                    <span>兜底标题</span>
                    <input v-model="form.default_title" type="text" />
                </label>
                <label class="field">
                    <span>兜底图片</span>
                    <div class="img-field">
                        <input
                            v-model="form.default_image_url"
                            type="text"
                            placeholder="图片地址，或点击右侧上传"
                        />
                        <input
                            ref="fallbackFileInput"
                            type="file"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="visually-hidden"
                            @change="onFallbackImageChange"
                        />
                        <button
                            type="button"
                            class="btn btn--ghost"
                            :disabled="fallbackUploading"
                            @click="openFallbackFilePicker"
                        >
                            {{ fallbackUploading ? '上传中…' : '上传图片' }}
                        </button>
                    </div>
                    <p v-if="fallbackUploadErr" class="field-err">{{ fallbackUploadErr }}</p>
                    <div v-if="form.default_image_url?.trim()" class="img-preview-frame">
                        <img
                            :key="fallbackImgBust"
                            :src="fallbackImageSrc()"
                            alt="兜底图预览"
                            class="img-preview"
                            @load="fallbackImgLoadError = false"
                            @error="fallbackImgLoadError = true"
                        />
                        <p v-if="fallbackImgLoadError" class="field-err">
                            预览加载失败。请执行
                            <code class="inline-code">php artisan storage:link</code>
                            ，或重启 Docker php 容器以创建
                            <code class="inline-code">public/storage</code>
                            软链。
                        </p>
                    </div>
                </label>
                <label class="field">
                    <span>兜底跳转链接</span>
                    <input v-model="form.default_link_url" type="text" />
                </label>
                <label class="field">
                    <span>兜底文案 / HTML</span>
                    <textarea v-model="form.default_content" rows="4" />
                </label>

                <div class="btns">
                    <button type="button" class="btn" @click="closeModal">取消</button>
                    <button v-if="showCreate" type="button" class="btn primary" @click="createSlot">创建</button>
                    <button v-else type="button" class="btn primary" @click="saveEdit">保存</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.35rem;
}
.head__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
}
.search {
    min-width: 180px;
    padding: 0.4rem 0.6rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.per-page {
    padding: 0.4rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
}
.link-out {
    font-size: 0.85rem;
    color: #2563eb;
    text-decoration: none;
}
.link-out:hover {
    text-decoration: underline;
}
.page-title {
    margin: 0;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.55;
}
.lead code {
    font-size: 0.8em;
    background: #e2e8f0;
    padding: 0.05rem 0.3rem;
    border-radius: 4px;
}
.lead a {
    color: #2563eb;
}
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.table th,
.table td {
    padding: 0.55rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
}
.muted {
    color: #94a3b8;
}
.acts {
    display: flex;
    gap: 0.5rem;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
}
.empty {
    padding: 1rem;
    color: #94a3b8;
    margin: 0;
}
.btn {
    padding: 0.45rem 0.9rem;
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
.btn--ghost {
    flex-shrink: 0;
    white-space: nowrap;
}
.img-field {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    align-items: center;
}
.img-field input[type='text'] {
    flex: 1;
    min-width: 8rem;
}
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
.field-err {
    margin: 0.25rem 0 0;
    font-size: 0.78rem;
    color: #b91c1c;
}
.img-preview-frame {
    margin-top: 0.4rem;
    padding: 0.35rem;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    background: #fafafa;
}
.img-preview {
    display: block;
    max-width: 100%;
    max-height: 120px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    object-fit: contain;
}
.inline-code {
    font-family: ui-monospace, monospace;
    font-size: 0.78em;
    background: #f1f5f9;
    padding: 0.05rem 0.25rem;
    border-radius: 4px;
}
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
    padding: 1rem;
}
.modal__box {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    width: 100%;
    max-width: 440px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box--lg {
    max-width: 520px;
}
.sub {
    margin: 1rem 0 0.5rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.sub:first-of-type {
    margin-top: 0;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
}
.field input,
.field select,
.field textarea {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
}
.field-hint {
    margin: 0 0 0.75rem;
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.45;
}
.row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
.btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
</style>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';

const posts = ref([]);
const meta = ref(null);
const total = ref(0);
const loadErr = ref('');
const busyId = ref(null);
const rejectFor = ref(null);
const rejectNote = ref('');
const selected = ref([]);
const batchRejectOpen = ref(false);

const selectedCount = computed(() => selected.value.length);

async function load(page = 1) {
    loadErr.value = '';
    try {
        const { data } = await axios.get('/api/admin/user-posts/pending', {
            params: { page },
        });
        posts.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
        const ids = new Set(posts.value.map((p) => p.id));
        selected.value = selected.value.filter((id) => ids.has(id));
    } catch {
        loadErr.value = '无法加载待审列表';
    }
}

onMounted(() => load(1));

const allSelectedOnPage = computed(
    () => posts.value.length > 0 && posts.value.every((p) => selected.value.includes(p.id))
);

function toggleSelect(id) {
    const i = selected.value.indexOf(id);
    if (i >= 0) {
        selected.value.splice(i, 1);
    } else {
        selected.value.push(id);
    }
}

function toggleSelectAll() {
    if (allSelectedOnPage.value) {
        const ids = posts.value.map((p) => p.id);
        selected.value = selected.value.filter((id) => !ids.includes(id));
    } else {
        const set = new Set(selected.value);
        posts.value.forEach((p) => set.add(p.id));
        selected.value = [...set];
    }
}

async function approve(id) {
    busyId.value = id;
    try {
        await axios.post(`/api/admin/user-posts/${id}/approve`);
        selected.value = selected.value.filter((x) => x !== id);
        await load(meta.value?.current_page ?? 1);
    } finally {
        busyId.value = null;
    }
}

async function batchApprove() {
    if (!selected.value.length) {
        return;
    }
    if (!confirm(`确定批量通过 ${selected.value.length} 条投稿？`)) {
        return;
    }
    busyId.value = -1;
    try {
        await axios.post('/api/admin/user-posts/batch-approve', { ids: [...selected.value] });
        selected.value = [];
        await load(meta.value?.current_page ?? 1);
    } finally {
        busyId.value = null;
    }
}

function openReject(id) {
    rejectFor.value = id;
    rejectNote.value = '';
}

function openBatchReject() {
    if (!selected.value.length) {
        return;
    }
    batchRejectOpen.value = true;
    rejectNote.value = '';
}

function closeReject() {
    rejectFor.value = null;
    rejectNote.value = '';
}

function closeBatchReject() {
    batchRejectOpen.value = false;
    rejectNote.value = '';
}

async function confirmReject() {
    if (!rejectFor.value) {
        return;
    }
    busyId.value = rejectFor.value;
    try {
        await axios.post(`/api/admin/user-posts/${rejectFor.value}/reject`, {
            audit_note: rejectNote.value,
        });
        selected.value = selected.value.filter((x) => x !== rejectFor.value);
        closeReject();
        await load(meta.value?.current_page ?? 1);
    } finally {
        busyId.value = null;
    }
}

async function confirmBatchReject() {
    if (rejectNote.value.trim().length < 2) {
        return;
    }
    busyId.value = -2;
    try {
        await axios.post('/api/admin/user-posts/batch-reject', {
            ids: [...selected.value],
            audit_note: rejectNote.value,
        });
        selected.value = [];
        closeBatchReject();
        await load(meta.value?.current_page ?? 1);
    } finally {
        busyId.value = null;
    }
}
</script>

<template>
    <div>
        <h1 class="page-title">审核投稿</h1>
        <p class="lead">对齐文档「审核管理模块」：单条 / 批量通过或拒绝（最多 50 条/次）。</p>

        <div v-if="!loadErr && posts.length" class="toolbar">
            <label class="check-all">
                <input type="checkbox" :checked="allSelectedOnPage" @change="toggleSelectAll" />
                本页全选
            </label>
            <span v-if="selectedCount" class="sel">已选 {{ selectedCount }}</span>
            <button
                type="button"
                class="btn btn--batch"
                :disabled="!selectedCount || busyId !== null"
                @click="batchApprove"
            >
                批量通过
            </button>
            <button
                type="button"
                class="btn btn--batch btn--no"
                :disabled="!selectedCount || busyId !== null"
                @click="openBatchReject"
            >
                批量拒绝
            </button>
        </div>

        <p v-if="loadErr" class="msg-err">{{ loadErr }}</p>
        <ul class="list">
            <li v-for="p in posts" :key="p.id" class="item">
                <label class="row-check">
                    <input
                        type="checkbox"
                        :checked="selected.includes(p.id)"
                        @change="toggleSelect(p.id)"
                    />
                </label>
                <div class="item__main">
                    <div class="item__title">{{ p.title }}</div>
                    <div class="item__meta">
                        {{ enumLabel('userPostType', p.type) }} · {{ p.author?.name || p.author?.email || '—' }}
                    </div>
                </div>
                <div class="item__actions">
                    <button
                        type="button"
                        class="btn btn--ok"
                        :disabled="busyId !== null"
                        @click="approve(p.id)"
                    >
                        通过
                    </button>
                    <button type="button" class="btn btn--no" @click="openReject(p.id)">拒绝</button>
                </div>
            </li>
        </ul>
        <p v-if="!loadErr && posts.length === 0" class="empty">暂无待审投稿</p>

        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            @update:page="load"
        />

        <div v-if="rejectFor" class="modal" @click.self="closeReject">
            <div class="modal__box" @click.stop>
                <h2 class="modal__title">拒绝原因</h2>
                <textarea
                    v-model="rejectNote"
                    class="modal__ta"
                    rows="4"
                    placeholder="至少 2 个字符"
                />
                <div class="modal__btns">
                    <button type="button" class="btn btn--ghost" @click="closeReject">取消</button>
                    <button
                        type="button"
                        class="btn btn--no"
                        :disabled="rejectNote.trim().length < 2 || busyId === rejectFor"
                        @click="confirmReject"
                    >
                        确认拒绝
                    </button>
                </div>
            </div>
        </div>

        <div v-if="batchRejectOpen" class="modal" @click.self="closeBatchReject">
            <div class="modal__box" @click.stop>
                <h2 class="modal__title">批量拒绝（{{ selectedCount }} 条）</h2>
                <textarea
                    v-model="rejectNote"
                    class="modal__ta"
                    rows="4"
                    placeholder="统一拒绝原因，至少 2 个字符"
                />
                <div class="modal__btns">
                    <button type="button" class="btn btn--ghost" @click="closeBatchReject">取消</button>
                    <button
                        type="button"
                        class="btn btn--no"
                        :disabled="rejectNote.trim().length < 2 || busyId === -2"
                        @click="confirmBatchReject"
                    >
                        确认批量拒绝
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
}
.toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1rem;
    padding: 0.65rem 0.85rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}
.check-all {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.85rem;
    cursor: pointer;
}
.sel {
    font-size: 0.85rem;
    color: #2563eb;
    font-weight: 600;
}
.btn--batch {
    padding: 0.35rem 0.75rem;
    font-size: 0.82rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    cursor: pointer;
}
.msg-err {
    color: #b91c1c;
}
.list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.item {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 0.65rem;
    background: #fff;
    padding: 1rem 1.1rem;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
}
.row-check {
    padding-top: 0.15rem;
    cursor: pointer;
}
.item__main {
    flex: 1;
    min-width: 0;
}
.item__title {
    font-weight: 600;
}
.item__meta {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.25rem;
}
.item__actions {
    display: flex;
    gap: 0.5rem;
}
.btn {
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    cursor: pointer;
    border: 1px solid transparent;
}
.btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}
.btn--ok {
    background: #16a34a;
    color: #fff;
}
.btn--no {
    background: #fff;
    border-color: #cbd5e1;
    color: #b91c1c;
}
.btn--ghost {
    background: #f1f5f9;
    border-color: #e2e8f0;
}
.empty {
    color: #64748b;
    margin-top: 1rem;
}
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 50;
}
.modal__box {
    background: #fff;
    border-radius: 10px;
    padding: 1.25rem;
    width: 100%;
    max-width: 420px;
}
.modal__title {
    margin: 0 0 0.75rem;
    font-size: 1.1rem;
}
.modal__ta {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}
.modal__btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
</style>

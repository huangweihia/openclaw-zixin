<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const status = ref('');
const q = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(25);
const loading = ref(false);
const err = ref('');
const detail = ref(null);
const detailErr = ref('');

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const params = { page, per_page: perPage.value };
        if (status.value) {
            params.status = status.value;
        }
        if (q.value.trim()) {
            params.q = q.value.trim();
        }
        const { data } = await axios.get('/api/admin/email-logs', { params });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(1));
watch([status, perPage], () => load(1));

let searchTimer;
function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => load(1), 350);
}

function onPerPageChange(next) {
    perPage.value = Number(next) || 25;
    load(1);
}

async function openDetail(r) {
    detail.value = null;
    detailErr.value = '';
    try {
        const { data } = await axios.get(`/api/admin/email-logs/${r.id}`);
        detail.value = data;
    } catch (e) {
        detailErr.value = e.response?.data?.message || '加载详情失败';
        detail.value = { _error: true };
    }
}

function closeDetail() {
    detail.value = null;
    detailErr.value = '';
}

function fmtTime(v) {
    return v ? String(v).slice(0, 19).replace('T', ' ') : '—';
}
</script>

<template>
    <AdminPageShell title="邮件发送记录" :loading="loading">
        <template #toolbar>
            <input
                v-model="q"
                class="search"
                type="search"
                placeholder="搜索收件人/主题/模板键"
                autocomplete="off"
                @input="onSearchInput"
            />
        </template>
        <el-tabs v-model="status" class="tabs" type="card">
            <el-tab-pane label="全部" name="" />
            <el-tab-pane label="待发送" name="pending" />
            <el-tab-pane label="已发送" name="sent" />
            <el-tab-pane label="发送失败" name="failed" />
        </el-tabs>
        <p v-if="err" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>收件人</th>
                        <th>主题</th>
                        <th>模板键</th>
                        <th>状态</th>
                        <th>时间</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.id }}</td>
                        <td>{{ r.to }}</td>
                        <td>{{ r.subject }}</td>
                        <td class="mono">{{ r.template_key || '—' }}</td>
                        <td>{{ enumLabel('emailLogStatus', r.status) }}</td>
                        <td class="muted">{{ r.created_at?.slice(0, 19)?.replace('T', ' ') }}</td>
                        <td>
                            <button type="button" class="link" @click="openDetail(r)">详情</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无记录</p>
        </AdminCard>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            :per-page="perPage"
            :loading="loading"
            @update:page="load"
            @update:per-page="onPerPageChange"
        />

        <div v-if="detail" class="modal" @click.self="closeDetail">
            <div class="modal__box" @click.stop>
                <h2>邮件记录详情</h2>
                <p v-if="detailErr" class="err">{{ detailErr }}</p>
                <template v-else-if="!detail._error">
                    <dl class="kv">
                        <dt>ID</dt>
                        <dd>{{ detail.id }}</dd>
                        <dt>收件人</dt>
                        <dd>{{ detail.to }}</dd>
                        <dt>主题</dt>
                        <dd>{{ detail.subject }}</dd>
                        <dt>模板键</dt>
                        <dd class="mono">{{ detail.template_key || '—' }}</dd>
                        <dt>状态</dt>
                        <dd>{{ enumLabel('emailLogStatus', detail.status) }}</dd>
                        <dt>用户</dt>
                        <dd>
                            <template v-if="detail.user">
                                {{ detail.user.name }}（{{ detail.user.email }}）
                            </template>
                            <template v-else>—</template>
                        </dd>
                        <dt>错误信息</dt>
                        <dd class="pre">{{ detail.error_message || '—' }}</dd>
                        <dt>发送时间</dt>
                        <dd>{{ fmtTime(detail.sent_at) }}</dd>
                        <dt>创建时间</dt>
                        <dd>{{ fmtTime(detail.created_at) }}</dd>
                        <dt>更新时间</dt>
                        <dd>{{ fmtTime(detail.updated_at) }}</dd>
                    </dl>
                </template>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeDetail">关闭</button>
                </div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.search {
    width: 100%;
    max-width: 360px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.95rem;
}
.tabs {
    margin-bottom: 0.75rem;
}
.err {
    color: #b91c1c;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}
.table th,
.table td {
    padding: 0.5rem 0.65rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.mono {
    font-family: ui-monospace, monospace;
}
.muted {
    color: #64748b;
    white-space: nowrap;
}
.empty {
    padding: 1rem;
    color: #94a3b8;
    margin: 0;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
    font-size: inherit;
}
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 80;
    padding: 1rem;
}
.modal__box {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box h2 {
    margin: 0 0 1rem;
    font-size: 1.1rem;
}
.kv {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 0.35rem 0.75rem;
    font-size: 0.86rem;
    margin: 0;
}
.kv dt {
    color: #64748b;
    margin: 0;
}
.kv dd {
    margin: 0;
    word-break: break-word;
}
.pre {
    white-space: pre-wrap;
    font-family: ui-monospace, monospace;
    font-size: 0.8rem;
}
.modal__btns {
    margin-top: 1rem;
    display: flex;
    justify-content: flex-end;
}
.btn {
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
</style>

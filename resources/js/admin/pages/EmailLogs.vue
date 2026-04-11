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
const detailOpen = ref(false);

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
    detailOpen.value = true;
    try {
        const { data } = await axios.get(`/api/admin/email-logs/${r.id}`);
        detail.value = data;
    } catch (e) {
        detailErr.value = e.response?.data?.message || '加载详情失败';
        detail.value = { _error: true };
    }
}

function closeDetail() {
    detailOpen.value = false;
}

function onDetailClosed() {
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
            <el-input
                v-model="q"
                class="oc-toolbar-search"
                clearable
                placeholder="搜索收件人/主题/模板键"
                @input="onSearchInput"
            />
        </template>
        <el-tabs v-model="status" class="oc-tabs" type="card">
            <el-tab-pane label="全部" name="" />
            <el-tab-pane label="待发送" name="pending" />
            <el-tab-pane label="已发送" name="sent" />
            <el-tab-pane label="发送失败" name="failed" />
        </el-tabs>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-c-alert" :title="err" />
        <AdminCard>
            <el-table v-loading="loading" :data="rows" stripe border style="width: 100%" empty-text="暂无记录">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column prop="to" label="收件人" min-width="140" show-overflow-tooltip />
                <el-table-column prop="subject" label="主题" min-width="160" show-overflow-tooltip />
                <el-table-column label="模板键" width="140" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span class="mono">{{ row.template_key || '—' }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="100">
                    <template #default="{ row }">{{ enumLabel('emailLogStatus', row.status) }}</template>
                </el-table-column>
                <el-table-column label="时间" width="168">
                    <template #default="{ row }">
                        <span class="muted">{{ row.created_at?.slice(0, 19)?.replace('T', ' ') }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="88" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openDetail(row)">详情</el-button>
                    </template>
                </el-table-column>
            </el-table>
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

        <el-dialog v-model="detailOpen" title="邮件记录详情" width="520px" destroy-on-close @closed="onDetailClosed">
            <el-alert v-if="detailErr" type="error" :closable="false" show-icon :title="detailErr" />
            <template v-else-if="detail && !detail._error">
                <el-descriptions :column="1" border size="small">
                    <el-descriptions-item label="ID">{{ detail.id }}</el-descriptions-item>
                    <el-descriptions-item label="收件人">{{ detail.to }}</el-descriptions-item>
                    <el-descriptions-item label="主题">{{ detail.subject }}</el-descriptions-item>
                    <el-descriptions-item label="模板键">
                        <span class="mono">{{ detail.template_key || '—' }}</span>
                    </el-descriptions-item>
                    <el-descriptions-item label="状态">{{ enumLabel('emailLogStatus', detail.status) }}</el-descriptions-item>
                    <el-descriptions-item label="用户">
                        <template v-if="detail.user">{{ detail.user.name }}（{{ detail.user.email }}）</template>
                        <template v-else>—</template>
                    </el-descriptions-item>
                    <el-descriptions-item label="错误信息">
                        <span class="pre-wrap">{{ detail.error_message || '—' }}</span>
                    </el-descriptions-item>
                    <el-descriptions-item label="发送时间">{{ fmtTime(detail.sent_at) }}</el-descriptions-item>
                    <el-descriptions-item label="创建时间">{{ fmtTime(detail.created_at) }}</el-descriptions-item>
                    <el-descriptions-item label="更新时间">{{ fmtTime(detail.updated_at) }}</el-descriptions-item>
                </el-descriptions>
            </template>
            <template #footer>
                <el-button @click="closeDetail">关闭</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-toolbar-search {
    width: 100%;
    max-width: 360px;
}
.oc-tabs {
    margin-bottom: 12px;
}
.oc-c-alert {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, monospace;
}
.muted {
    color: var(--el-text-color-secondary);
    white-space: nowrap;
}
.pre-wrap {
    white-space: pre-wrap;
    font-family: ui-monospace, monospace;
    font-size: 12px;
}
</style>

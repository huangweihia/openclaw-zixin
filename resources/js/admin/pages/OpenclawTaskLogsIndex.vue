<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import { enumLabel, enumOptions } from '../constants/labels';

const rows = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const err = ref('');
const loading = ref(false);

// 统计图表（最近 N 天）
const days = ref(7);
const chartLoading = ref(false);
const chartErr = ref('');
const chartPoints = ref([]); // { d, task_type, status, n }

const q = ref('');
const taskType = ref('');
const status = ref('');
const pushStatus = ref('');
const from = ref('');
const to = ref('');

const showDetail = ref(false);
const detail = ref(null);
const detailErr = ref('');

const typeOpts = [{ value: '', label: '全部类型' }, ...enumOptions('openclawTaskType')];
const statusOpts = [{ value: '', label: '全部状态' }, ...enumOptions('openclawTaskStatus')];
const pushOpts = [{ value: '', label: '全部推送' }, ...enumOptions('openclawPushStatus')];

let searchT;

function formatDt(s) {
    if (!s) return '—';
    try {
        const d = new Date(s);
        if (Number.isNaN(d.getTime())) return String(s);
        return d.toLocaleString();
    } catch {
        return String(s);
    }
}

function resetPageMetaFrom(data) {
    meta.value = {
        current_page: data.current_page ?? 1,
        last_page: data.last_page ?? 1,
        total: data.total ?? 0,
    };
}

async function load(page = 1) {
    loading.value = true;
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/openclaw-task-logs', {
            params: {
                page,
                q: q.value.trim() || undefined,
                task_type: taskType.value || undefined,
                status: status.value || undefined,
                push_status: pushStatus.value || undefined,
                from: from.value || undefined,
                to: to.value || undefined,
            },
        });
        rows.value = data.data ?? [];
        resetPageMetaFrom(data);
    } catch (e) {
        const st = e.response?.status;
        const msg = e.response?.data?.message || e.message || '';
        err.value = st ? `加载失败（HTTP ${st}）${msg ? `：${msg}` : ''}` : `加载失败：${msg || '网络或服务异常'}`;
    } finally {
        loading.value = false;
    }
}

function onSearchLike() {
    clearTimeout(searchT);
    searchT = setTimeout(() => load(1), 250);
}

const hasFilters = computed(() => {
    return Boolean(q.value.trim() || taskType.value || status.value || pushStatus.value || from.value || to.value);
});

function resetFilters() {
    q.value = '';
    taskType.value = '';
    status.value = '';
    pushStatus.value = '';
    from.value = '';
    to.value = '';
    load(1);
}

const statusOrder = ['success', 'error', 'timeout', 'skipped'];
const statusColor = {
    success: '#22c55e',
    error: '#ef4444',
    timeout: '#f59e0b',
    skipped: '#94a3b8',
};

const barHeightPx = 160;

const byTypeStatus = computed(() => {
    /** @type {Record<string, Record<string, number>>} */
    const m = {};
    for (const p of chartPoints.value) {
        const t = p.task_type || 'unknown';
        const s = p.status || 'skipped';
        if (!m[t]) m[t] = {};
        m[t][s] = (m[t][s] || 0) + Number(p.n || 0);
    }
    return m;
});

const byTypeTotals = computed(() => {
    /** @type {Record<string, number>} */
    const out = {};
    for (const [t, ss] of Object.entries(byTypeStatus.value)) {
        out[t] = statusOrder.reduce((sum, s) => sum + (ss[s] || 0), 0);
    }
    return out;
});

const trendByDay = computed(() => {
    /** @type {Record<string, Record<string, number>>} */
    const m = {};
    for (const p of chartPoints.value) {
        const d = p.d || '';
        const s = p.status || 'skipped';
        if (!d) continue;
        if (!m[d]) m[d] = {};
        m[d][s] = (m[d][s] || 0) + Number(p.n || 0);
    }
    const daysSorted = Object.keys(m).sort((a, b) => (a < b ? -1 : a > b ? 1 : 0));
    const maxTotal = Math.max(
        0,
        ...daysSorted.map((d) => statusOrder.reduce((sum, s) => sum + (m[d]?.[s] || 0), 0)),
    );

    return { m, daysSorted, maxTotal };
});

function segmentBottomPercent(day, idx) {
    const maxTotal = trendByDay.value.maxTotal;
    if (!maxTotal || maxTotal <= 0) return 0;
    let sum = 0;
    for (let i = 0; i < idx; i++) {
        const s = statusOrder[i];
        sum += trendByDay.value.m[day]?.[s] || 0;
    }
    return (sum / maxTotal) * 100;
}

async function loadCharts() {
    chartErr.value = '';
    chartLoading.value = true;
    try {
        const { data } = await axios.get('/api/admin/openclaw-task-logs/stats', { params: { days: days.value } });
        chartPoints.value = data?.stats ?? [];
    } catch (e) {
        const st = e.response?.status;
        const detail = e.response?.data?.message || e.message || '';
        chartErr.value = st ? `加载失败（HTTP ${st}）${detail ? `：${detail}` : ''}` : `加载失败：${detail || '网络或服务异常'}`;
        chartPoints.value = [];
    } finally {
        chartLoading.value = false;
    }
}

async function openRow(r) {
    showDetail.value = true;
    detail.value = null;
    detailErr.value = '';
    try {
        const { data } = await axios.get(`/api/admin/openclaw-task-logs/${r.id}`);
        detail.value = data.log ?? null;
    } catch (e) {
        const st = e.response?.status;
        const msg = e.response?.data?.message || e.message || '';
        detailErr.value = st ? `加载失败（HTTP ${st}）${msg ? `：${msg}` : ''}` : `加载失败：${msg || '网络或服务异常'}`;
    }
}

async function removeRow(r) {
    if (!confirm(`确定删除该条日志？\n\n#${r.id} ${r.task_name}`)) return;
    err.value = '';
    try {
        await axios.delete(`/api/admin/openclaw-task-logs/${r.id}`);
        await load(meta.value.current_page || 1);
    } catch (e) {
        err.value = e.response?.data?.message || '删除失败';
    }
}

watch([taskType, status, pushStatus, from, to], () => load(1));
watch(days, () => loadCharts());
onMounted(async () => {
    await Promise.all([load(1), loadCharts()]);
});
</script>

<template>
    <AdminPageShell
        title="OpenClaw 任务日志"
        lead="记录 OpenClaw 定时任务执行情况（success / error / timeout / skipped），用于排障与数据量核对。"
    >
        <template #actions>
            <button type="button" class="btn" :disabled="loading" @click="load(meta.current_page || 1)">
                刷新
            </button>
        </template>

        <div class="card chart-card">
            <div class="chart-card__head">
                <div>
                    <h2 class="chart-card__title">统计图表</h2>
                    <p class="chart-card__sub">按最近 N 天汇总：任务类型 × 状态，以及按天趋势（堆叠柱状）。</p>
                </div>
                <div class="chart-card__controls">
                    <label class="chart-select">
                        <span class="chart-select__label">统计天数</span>
                        <select v-model="days">
                            <option :value="7">7 天</option>
                            <option :value="14">14 天</option>
                            <option :value="30">30 天</option>
                        </select>
                    </label>
                    <button type="button" class="btn" :disabled="chartLoading" @click="loadCharts">
                        {{ chartLoading ? '加载中…' : '刷新统计' }}
                    </button>
                </div>
            </div>
            <p v-if="chartErr" class="err">{{ chartErr }}</p>

            <div class="chart-grid">
                <div class="chart-block">
                    <h3 class="chart-block__title">任务类型 × 状态（堆叠条）</h3>
                    <div class="chart-block__body">
                        <div v-if="chartLoading" class="chart-skel">加载中…</div>
                        <template v-else>
                            <div v-if="Object.keys(byTypeStatus).length === 0" class="muted">暂无统计数据</div>
                            <div v-else class="type-bars">
                                <div v-for="t in Object.keys(byTypeStatus)" :key="t" class="type-bar">
                                    <div class="type-bar__l">
                                        {{ enumLabel('openclawTaskType', t) }}
                                    </div>
                                    <div class="type-bar__track">
                                        <div
                                            v-for="s in statusOrder"
                                            :key="s"
                                            class="type-bar__seg"
                                            :style="{
                                                width: `${byTypeTotals[t] > 0 ? ((byTypeStatus[t]?.[s] || 0) / byTypeTotals[t]) * 100 : 0}%`,
                                                background: statusColor[s],
                                            }"
                                            :title="`${enumLabel('openclawTaskStatus', s)}: ${byTypeStatus[t]?.[s] || 0}`"
                                        />
                                    </div>
                                    <div class="type-bar__r mono">{{ byTypeTotals[t] || 0 }}</div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="chart-block">
                    <h3 class="chart-block__title">最近 N 天趋势（堆叠柱）</h3>
                    <div class="chart-block__body">
                        <div v-if="chartLoading" class="chart-skel">加载中…</div>
                        <template v-else>
                            <div v-if="trendByDay.daysSorted.length === 0" class="muted">暂无趋势数据</div>
                            <div v-else class="trend">
                                <div class="trend__bars">
                                    <div v-for="d in trendByDay.daysSorted" :key="d" class="trend__col">
                                        <div class="trend__stack" :style="{ height: `${barHeightPx}px` }">
                                            <div
                                                v-for="(s, idx) in statusOrder"
                                                :key="s"
                                                class="trend__seg"
                                                :style="{
                                                    background: statusColor[s],
                                                    height: `${trendByDay.maxTotal > 0 ? ((trendByDay.m[d]?.[s] || 0) / trendByDay.maxTotal) * 100 : 0}%`,
                                                    bottom: `${segmentBottomPercent(d, idx)}%`,
                                                }"
                                                :title="`${d} - ${enumLabel('openclawTaskStatus', s)}: ${trendByDay.m[d]?.[s] || 0}`"
                                            />
                                        </div>
                                        <div class="trend__label">{{ d.slice(5) }}</div>
                                    </div>
                                </div>
                                <div class="trend__legend">
                                    <span v-for="s in statusOrder" :key="s" class="legend">
                                        <i class="legend__dot" :style="{ background: statusColor[s] }" /> {{ enumLabel('openclawTaskStatus', s) }}
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="filters card">
            <input v-model="q" class="search" type="search" placeholder="任务名 / task_id / endpoint / 错误信息" @input="onSearchLike" />
            <div class="row">
                <label class="field">
                    <span>类型</span>
                    <select v-model="taskType">
                        <option v-for="o in typeOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>状态</span>
                    <select v-model="status">
                        <option v-for="o in statusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="field">
                    <span>推送</span>
                    <select v-model="pushStatus">
                        <option v-for="o in pushOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
            </div>
            <div class="row">
                <label class="field">
                    <span>开始日期（从）</span>
                    <input v-model="from" type="date" />
                </label>
                <label class="field">
                    <span>开始日期（到）</span>
                    <input v-model="to" type="date" />
                </label>
                <div class="field field--btns">
                    <span>&nbsp;</span>
                    <button v-if="hasFilters" type="button" class="btn" @click="resetFilters">清空筛选</button>
                </div>
            </div>
        </div>

        <p v-if="err" class="err">{{ err }}</p>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>任务</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th>耗时</th>
                        <th>数据量</th>
                        <th>开始时间</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td class="mono">{{ r.id }}</td>
                        <td class="clip">
                            <button type="button" class="link" @click="openRow(r)">{{ r.task_name }}</button>
                            <div class="muted mono small" v-if="r.task_id">{{ r.task_id }}</div>
                        </td>
                        <td>{{ enumLabel('openclawTaskType', r.task_type) }}</td>
                        <td>
                            <span class="pill" :class="`pill--${r.status}`">{{ enumLabel('openclawTaskStatus', r.status) }}</span>
                        </td>
                        <td class="mono">{{ r.duration_ms != null ? `${r.duration_ms}ms` : '—' }}</td>
                        <td class="mono">{{ r.total_items ?? 0 }}</td>
                        <td class="mono">{{ formatDt(r.started_at) }}</td>
                        <td class="acts">
                            <button type="button" class="link danger" @click="removeRow(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && rows.length === 0" class="empty">暂无记录</p>
        </div>

        <AdminPagination
            v-if="meta && meta.last_page > 1"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="meta.total"
            :loading="loading"
            @update:page="load"
        />

        <div v-if="showDetail" class="modal" @click.self="showDetail = false">
            <div class="modal__box" @click.stop>
                <div class="modal__head">
                    <h2 class="modal__title">日志详情</h2>
                    <button type="button" class="btn btn--ghost" @click="showDetail = false">关闭</button>
                </div>
                <p v-if="detailErr" class="err">{{ detailErr }}</p>
                <template v-else-if="detail">
                    <div class="kv">
                        <div class="kv__row"><span>任务名</span><span class="mono">{{ detail.task_name }}</span></div>
                        <div class="kv__row"><span>Task ID</span><span class="mono">{{ detail.task_id || '—' }}</span></div>
                        <div class="kv__row"><span>类型</span><span>{{ enumLabel('openclawTaskType', detail.task_type) }}</span></div>
                        <div class="kv__row"><span>状态</span><span>{{ enumLabel('openclawTaskStatus', detail.status) }}</span></div>
                        <div class="kv__row"><span>耗时</span><span class="mono">{{ detail.duration_ms != null ? `${detail.duration_ms}ms` : '—' }}</span></div>
                        <div class="kv__row"><span>总数据</span><span class="mono">{{ detail.total_items ?? 0 }}</span></div>
                        <div class="kv__row"><span>成功/失败/跳过</span><span class="mono">{{ detail.success_count ?? 0 }}/{{ detail.failed_count ?? 0 }}/{{ detail.skipped_count ?? 0 }}</span></div>
                        <div class="kv__row"><span>接口</span><span class="mono">{{ detail.api_endpoint || '—' }}</span></div>
                        <div class="kv__row"><span>推送状态</span><span>{{ enumLabel('openclawPushStatus', detail.push_status) }}</span></div>
                        <div class="kv__row"><span>开始</span><span class="mono">{{ formatDt(detail.started_at) }}</span></div>
                        <div class="kv__row"><span>结束</span><span class="mono">{{ formatDt(detail.finished_at) }}</span></div>
                    </div>
                    <div v-if="detail.error_message" class="block">
                        <h3 class="block__title">错误信息</h3>
                        <pre class="pre">{{ detail.error_message }}</pre>
                    </div>
                    <div v-if="detail.error_details" class="block">
                        <h3 class="block__title">错误详情</h3>
                        <pre class="pre">{{ detail.error_details }}</pre>
                    </div>
                    <div v-if="detail.push_response" class="block">
                        <h3 class="block__title">推送响应</h3>
                        <pre class="pre">{{ detail.push_response }}</pre>
                    </div>
                    <div v-if="detail.data_summary" class="block">
                        <h3 class="block__title">数据汇总</h3>
                        <pre class="pre">{{ JSON.stringify(detail.data_summary, null, 2) }}</pre>
                    </div>
                </template>
                <p v-else class="muted">加载中…</p>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 0.35rem;
}
.head__actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
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
.chart-card {
    margin-bottom: 0.9rem;
}
.chart-card__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 0.75rem;
}
.chart-card__title {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
}
.chart-card__sub {
    margin: 0;
    font-size: 0.82rem;
    color: #64748b;
}
.chart-card__controls {
    display: flex;
    gap: 0.75rem;
    align-items: flex-end;
}
.chart-select {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.chart-select__label {
    font-size: 0.8rem;
    color: #64748b;
}
.chart-select select {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
}
.chart-grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 1rem;
}
@media (max-width: 900px) {
    .chart-grid {
        grid-template-columns: 1fr;
    }
}
.chart-block__title {
    margin: 0 0 0.65rem;
    font-size: 0.85rem;
    font-weight: 800;
    color: #334155;
}
.chart-block__body {
    min-height: 240px;
}
.chart-skel {
    font-size: 0.85rem;
    color: #94a3b8;
}
.type-bars {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}
.type-bar {
    display: grid;
    grid-template-columns: 12rem 1fr 5rem;
    gap: 0.75rem;
    align-items: center;
}
@media (max-width: 900px) {
    .type-bar {
        grid-template-columns: 10rem 1fr 4.5rem;
    }
}
.type-bar__l {
    font-size: 0.85rem;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.type-bar__track {
    height: 14px;
    border-radius: 999px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    display: flex;
}
.type-bar__seg {
    height: 100%;
    flex-shrink: 0;
}
.type-bar__r {
    text-align: right;
    font-size: 0.85rem;
    color: #475569;
}

.trend__bars {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: 44px;
    gap: 12px;
    align-items: end;
    overflow-x: auto;
    padding-bottom: 0.25rem;
}
.trend__col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    min-width: 44px;
}
.trend__stack {
    width: 26px;
    position: relative;
    background: transparent;
}
.trend__seg {
    position: absolute;
    left: 0;
    right: 0;
    border-radius: 6px 6px 0 0;
}
.trend__label {
    font-size: 0.72rem;
    color: #64748b;
    white-space: nowrap;
}
.trend__legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.65rem;
}
.legend {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #64748b;
    font-size: 0.8rem;
}
.legend__dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
    border: 1px solid rgba(0, 0, 0, 0.08);
}
.card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: auto;
}
.filters {
    padding: 0.9rem 1rem;
    margin-bottom: 0.9rem;
}
.search {
    width: 100%;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    margin-bottom: 0.75rem;
}
.row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 0.6rem;
}
@media (max-width: 900px) {
    .row {
        grid-template-columns: 1fr;
    }
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    font-size: 0.82rem;
    color: #475569;
}
.field input,
.field select {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.field--btns {
    align-items: flex-start;
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
    vertical-align: top;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
}
.mono {
    font-family: ui-monospace, monospace;
}
.small {
    font-size: 0.75rem;
}
.muted {
    color: #94a3b8;
}
.clip {
    max-width: 360px;
}
.acts {
    white-space: nowrap;
}
.link {
    background: none;
    border: none;
    padding: 0;
    color: #2563eb;
    cursor: pointer;
    font-size: inherit;
}
.link.danger {
    color: #b91c1c;
}
.btn {
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.85rem;
}
.btn--ghost {
    border-color: transparent;
    background: #f8fafc;
}
.pill {
    display: inline-flex;
    align-items: center;
    padding: 0.18rem 0.55rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
}
.pill--success {
    border-color: #bbf7d0;
    background: #f0fdf4;
    color: #166534;
}
.pill--error,
.pill--timeout {
    border-color: #fecaca;
    background: #fef2f2;
    color: #991b1b;
}
.pill--skipped {
    border-color: #e2e8f0;
    background: #f8fafc;
    color: #475569;
}
.empty {
    padding: 1rem;
    margin: 0;
    color: #94a3b8;
}
.err {
    color: #b91c1c;
}
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 90;
    padding: 1rem;
}
.modal__box {
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    overflow: auto;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
    padding: 1rem 1.1rem 1.1rem;
}
.modal__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.modal__title {
    margin: 0;
    font-size: 1.05rem;
}
.kv {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.4rem 1rem;
    font-size: 0.85rem;
    margin-bottom: 0.75rem;
}
@media (max-width: 860px) {
    .kv {
        grid-template-columns: 1fr;
    }
}
.kv__row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.35rem 0.5rem;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    background: #fafafa;
}
.block {
    margin-top: 0.75rem;
}
.block__title {
    margin: 0 0 0.35rem;
    font-size: 0.8rem;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.pre {
    margin: 0;
    padding: 0.75rem;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #0b1220;
    color: #e2e8f0;
    overflow: auto;
    font-size: 0.78rem;
    line-height: 1.45;
}
</style>


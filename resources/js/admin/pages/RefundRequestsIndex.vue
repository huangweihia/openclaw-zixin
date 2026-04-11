<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const refundStatusOpts = enumOptions('refundStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');
const dialogOpen = ref(false);
const editing = ref(null);
const form = ref({ status: 'pending', admin_note: '' });

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待处理' },
    { value: 'approved', label: '已通过' },
    { value: 'rejected', label: '已拒绝' },
    { value: 'completed', label: '已完成' },
];

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/refund-requests', {
            params: { page, status: status.value || undefined },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

watch(status, () => load(1));

function closeEdit() {
    dialogOpen.value = false;
    editing.value = null;
    err.value = '';
}

function openEdit(r) {
    err.value = '';
    editing.value = r;
    form.value = { status: r.status, admin_note: r.admin_note || '' };
    dialogOpen.value = true;
}

async function save() {
    err.value = '';
    try {
        await axios.put(`/api/admin/refund-requests/${editing.value.id}`, form.value);
        closeEdit();
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '保存失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="退款申请" lead="表 refund_requests：处理状态与管理员备注。">
        <template #toolbar>
            <el-tabs v-model="status" class="oc-tabs" type="card">
                <el-tab-pane v-for="t in tabs" :key="t.value || 'all'" :label="t.label" :name="t.value" />
            </el-tabs>
        </template>
        <el-alert v-if="err && !dialogOpen" type="error" :closable="false" show-icon class="oc-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无">
                <el-table-column label="订单" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span class="mono">{{ row.order?.order_no }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="用户" width="120" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.user?.name || '—' }}
                    </template>
                </el-table-column>
                <el-table-column prop="refund_amount" label="金额" width="100" />
                <el-table-column label="状态" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('refundStatus', row.status) }}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="100" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openEdit(row)">处理</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </AdminCard>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            @update:page="load"
        />

        <el-dialog
            v-model="dialogOpen"
            :title="editing ? `退款 #${editing.id}` : ''"
            width="480px"
            destroy-on-close
            align-center
            @closed="
                () => {
                    editing = null;
                    err = '';
                }
            "
        >
            <el-alert v-if="err && dialogOpen" type="error" :closable="false" show-icon class="mb-3" :title="err" />
            <el-text v-if="editing" type="info" size="small" class="mb-3 block">{{ editing.description }}</el-text>
            <el-form v-if="editing" label-position="top">
                <el-form-item label="状态">
                    <el-select v-model="form.status" class="w-full" placeholder="状态">
                        <el-option v-for="o in refundStatusOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="管理员备注">
                    <el-input v-model="form.admin_note" type="textarea" :rows="3" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="closeEdit">取消</el-button>
                <el-button type="primary" @click="save">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-tabs {
    margin-bottom: 0;
}
.oc-alert {
    margin-bottom: 12px;
}
.mb-3 {
    margin-bottom: 12px;
}
.block {
    display: block;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
.w-full {
    width: 100%;
}
</style>

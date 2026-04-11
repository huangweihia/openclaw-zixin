<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const invoiceStatusOpts = enumOptions('invoiceStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');
const dialogOpen = ref(false);
const editing = ref(null);
const form = ref({ status: 'pending', admin_note: '', invoice_file: '' });

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待开' },
    { value: 'issued', label: '已开具' },
    { value: 'rejected', label: '已拒绝' },
];

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/invoice-requests', {
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
    form.value = {
        status: r.status,
        admin_note: r.admin_note || '',
        invoice_file: r.invoice_file || '',
    };
    dialogOpen.value = true;
}

async function save() {
    err.value = '';
    try {
        await axios.put(`/api/admin/invoice-requests/${editing.value.id}`, form.value);
        closeEdit();
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '保存失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="发票申请" lead="表 invoice_requests。">
        <template #toolbar>
            <el-radio-group v-model="status" size="default">
                <el-radio-button v-for="t in tabs" :key="t.value || 'all'" :value="t.value">
                    {{ t.label }}
                </el-radio-button>
            </el-radio-group>
        </template>
        <el-alert v-if="err && !dialogOpen" type="error" :closable="false" show-icon class="oc-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无">
                <el-table-column label="订单" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span class="mono">{{ row.order?.order_no }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="email" label="邮箱" min-width="160" show-overflow-tooltip />
                <el-table-column label="类型" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('invoiceRequestType', row.invoice_type) }}
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('invoiceStatus', row.status) }}
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
            :title="editing ? `发票 #${editing.id}` : ''"
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
            <el-form v-if="editing" label-position="top">
                <el-form-item label="状态">
                    <el-select v-model="form.status" class="w-full" placeholder="状态">
                        <el-option v-for="o in invoiceStatusOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="发票文件 URL">
                    <el-input v-model="form.invoice_file" clearable />
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
.oc-alert {
    margin-bottom: 12px;
}
.mb-3 {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
.w-full {
    width: 100%;
}
</style>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const orderStatusOpts = enumOptions('orderStatus');
const orderTabs = computed(() => [{ value: '', label: '全部' }, ...orderStatusOpts]);

const status = ref('');
const q = ref('');
const orders = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(25);
const loading = ref(false);
const loadErr = ref('');
const formErr = ref('');
const dialogOpen = ref(false);
const editing = ref(null);
const form = ref({
    status: 'pending',
    remark: '',
    payment_method: '',
    payment_id: '',
    paid_at: '',
});

async function load(page = 1) {
    loadErr.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/orders', {
            params: { page, status: status.value || undefined, per_page: perPage.value, q: q.value || undefined },
        });
        orders.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
    } catch {
        loadErr.value = '无法加载订单';
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

async function openEdit(o) {
    formErr.value = '';
    try {
        const { data } = await axios.get(`/api/admin/orders/${o.id}`);
        const ord = data.order;
        editing.value = ord;
        form.value = {
            status: ord.status,
            remark: ord.remark || '',
            payment_method: ord.payment_method || '',
            payment_id: ord.payment_id || '',
            paid_at: ord.paid_at ? ord.paid_at.slice(0, 16) : '',
        };
        dialogOpen.value = true;
    } catch {
        formErr.value = '加载订单失败';
    }
}

function closeEdit() {
    dialogOpen.value = false;
    editing.value = null;
    formErr.value = '';
}

async function saveOrder() {
    formErr.value = '';
    try {
        await axios.put(`/api/admin/orders/${editing.value.id}`, {
            status: form.value.status,
            remark: form.value.remark || null,
            payment_method: form.value.payment_method || null,
            payment_id: form.value.payment_id || null,
            paid_at: form.value.paid_at || null,
        });
        closeEdit();
        await load(meta.value?.current_page ?? 1);
    } catch {
        formErr.value = '保存失败';
    }
}
</script>

<template>
    <AdminPageShell title="订单管理" :loading="loading">
        <template #toolbar>
            <el-input
                v-model="q"
                clearable
                placeholder="搜索订单号/用户邮箱"
                style="width: 280px"
                @input="onSearchInput"
            />
        </template>
        <el-radio-group v-model="status" size="default" class="oc-order-tabs">
            <el-radio-button v-for="t in orderTabs" :key="t.value || 'all'" :value="t.value">
                {{ t.label }}
            </el-radio-button>
        </el-radio-group>
        <el-alert v-if="loadErr" type="error" :closable="false" show-icon class="oc-o-alert" :title="loadErr" />
        <el-alert
            v-if="formErr && !dialogOpen"
            type="error"
            :closable="false"
            show-icon
            class="oc-o-alert"
            :title="formErr"
        />
        <AdminCard>
            <el-table :data="orders" stripe border style="width: 100%" empty-text="暂无订单">
                <el-table-column label="订单号" min-width="160" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span class="mono">{{ row.order_no }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="用户" min-width="180" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span v-if="row.user">{{ row.user.name }}（{{ row.user.email }}）</span>
                        <span v-else>—</span>
                    </template>
                </el-table-column>
                <el-table-column prop="amount" label="金额" width="100" />
                <el-table-column label="状态" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('orderStatus', row.status) }}
                    </template>
                </el-table-column>
                <el-table-column label="商品" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ enumLabel('orderProductType', row.product_type) }} #{{ row.product_id }}
                    </template>
                </el-table-column>
                <el-table-column label="创建时间" width="160">
                    <template #default="{ row }">
                        <span class="muted">{{ row.created_at?.slice(0, 16)?.replace('T', ' ') }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="88" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openEdit(row)">编辑</el-button>
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

        <el-dialog
            v-model="dialogOpen"
            :title="editing ? `订单 ${editing.order_no}` : ''"
            width="520px"
            destroy-on-close
            align-center
            @closed="
                () => {
                    editing = null;
                    formErr = '';
                }
            "
        >
            <el-alert v-if="formErr && dialogOpen" type="error" :closable="false" show-icon class="mb-3" :title="formErr" />
            <el-text v-if="editing" type="info" size="small" class="mb-3 block">
                金额 {{ editing.amount }} · 用户 {{ editing.user?.name }}
            </el-text>
            <el-form v-if="editing" label-position="top">
                <el-form-item label="状态">
                    <el-select v-model="form.status" class="w-full">
                        <el-option v-for="o in orderStatusOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input v-model="form.remark" type="textarea" :rows="2" />
                </el-form-item>
                <el-form-item label="支付方式">
                    <el-input v-model="form.payment_method" clearable />
                </el-form-item>
                <el-form-item label="支付 ID">
                    <el-input v-model="form.payment_id" clearable />
                </el-form-item>
                <el-form-item label="支付时间">
                    <el-input v-model="form.paid_at" type="datetime-local" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="closeEdit">取消</el-button>
                <el-button type="primary" @click="saveOrder">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-order-tabs {
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.oc-o-alert {
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
.muted {
    color: var(--el-text-color-secondary);
    font-size: 12px;
}
.w-full {
    width: 100%;
}
</style>

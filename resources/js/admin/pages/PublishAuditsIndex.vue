<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const publishAuditStatusOpts = enumOptions('publishAuditStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');
const dialogOpen = ref(false);
const editing = ref(null);
const form = ref({
    status: 'pending',
    reject_reason: '',
    suggest: '',
    priority: 0,
});

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待审' },
    { value: 'approved', label: '通过' },
    { value: 'rejected', label: '拒绝' },
];

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/publish-audits', {
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
        reject_reason: r.reject_reason || '',
        suggest: r.suggest || '',
        priority: r.priority ?? 0,
    };
    dialogOpen.value = true;
}

async function save() {
    err.value = '';
    try {
        await axios.put(`/api/admin/publish-audits/${editing.value.id}`, {
            status: form.value.status,
            reject_reason: form.value.reject_reason || null,
            suggest: form.value.suggest || null,
            priority: Number(form.value.priority) || 0,
        });
        closeEdit();
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '保存失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="发布审核记录" lead="表 publish_audits（与投稿审核配合，可手工纠偏）。">
        <template #toolbar>
            <el-radio-group v-model="status" size="default">
                <el-radio-button v-for="t in tabs" :key="t.value || 'all'" :value="t.value">
                    {{ t.label }}
                </el-radio-button>
            </el-radio-group>
        </template>
        <el-alert v-if="err && !dialogOpen" type="error" :closable="false" show-icon class="oc-pa-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无">
                <el-table-column label="投稿" min-width="200" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.user_post?.title || `#${row.publish_id}` }}
                    </template>
                </el-table-column>
                <el-table-column label="用户" width="120" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.user?.name || '—' }}
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="100">
                    <template #default="{ row }">
                        {{ enumLabel('publishAuditStatus', row.status) }}
                    </template>
                </el-table-column>
                <el-table-column prop="priority" label="优先级" width="88" />
                <el-table-column label="操作" width="100" fixed="right">
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
            @update:page="load"
        />

        <el-dialog
            v-model="dialogOpen"
            :title="editing ? `审核记录 #${editing.id}` : ''"
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
                        <el-option
                            v-for="o in publishAuditStatusOpts"
                            :key="o.value"
                            :label="o.label"
                            :value="o.value"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item label="优先级">
                    <el-input-number v-model="form.priority" :min="0" :step="1" controls-position="right" class="w-full" />
                </el-form-item>
                <el-form-item label="拒绝原因">
                    <el-input v-model="form.reject_reason" type="textarea" :rows="2" />
                </el-form-item>
                <el-form-item label="修改建议">
                    <el-input v-model="form.suggest" type="textarea" :rows="2" />
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
.oc-pa-alert {
    margin-bottom: 12px;
}
.mb-3 {
    margin-bottom: 12px;
}
.w-full {
    width: 100%;
}
</style>

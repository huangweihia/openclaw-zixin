<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const router = useRouter();
const q = ref('');
const users = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(20);
const loadErr = ref('');
const loading = ref(false);
const role = ref('');
const isBanned = ref('');

async function load(page = 1) {
    loading.value = true;
    loadErr.value = '';
    try {
        const { data } = await axios.get('/api/admin/users', {
            params: {
                page,
                per_page: perPage.value,
                q: q.value || undefined,
                role: role.value || undefined,
                is_banned: isBanned.value === '' ? undefined : isBanned.value,
            },
        });
        users.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
    } catch {
        loadErr.value = '无法加载用户列表';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(1));

let searchTimer;
function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => load(1), 350);
}

function goEdit(id) {
    router.push({ name: 'user-edit', params: { id: String(id) } });
}

function onPerPageChange(next) {
    perPage.value = Number(next) || 20;
    load(1);
}
</script>

<template>
    <AdminPageShell title="用户管理" lead="支持按角色/状态筛选，点击编辑进入详细配置页。" :loading="loading">
        <template #toolbar>
            <el-input
                v-model="q"
                clearable
                placeholder="按邮箱或昵称搜索"
                style="width: 220px"
                @input="onSearchInput"
            />
            <el-select v-model="role" placeholder="全部角色" clearable style="width: 140px" @change="load(1)">
                <el-option label="全部角色" value="" />
                <el-option label="普通用户" value="user" />
                <el-option label="VIP" value="vip" />
                <el-option label="SVIP" value="svip" />
                <el-option label="管理员" value="admin" />
            </el-select>
            <el-select v-model="isBanned" placeholder="全部状态" clearable style="width: 130px" @change="load(1)">
                <el-option label="全部状态" value="" />
                <el-option label="正常" value="0" />
                <el-option label="已禁用" value="1" />
            </el-select>
        </template>
        <el-alert v-if="loadErr" type="error" :closable="false" show-icon class="oc-u-alert" :title="loadErr" />
        <AdminCard>
            <el-table v-loading="loading" :data="users" stripe border style="width: 100%" empty-text="暂无用户">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column prop="name" label="昵称" min-width="100" show-overflow-tooltip />
                <el-table-column prop="email" label="邮箱" min-width="180" show-overflow-tooltip />
                <el-table-column label="角色" width="100">
                    <template #default="{ row }">
                        {{ enumLabel('userRole', row.role) }}
                    </template>
                </el-table-column>
                <el-table-column label="积分" width="88">
                    <template #default="{ row }">
                        {{ Number(row.points_balance || 0) }}
                    </template>
                </el-table-column>
                <el-table-column label="会员到期" width="120">
                    <template #default="{ row }">
                        {{ row.subscription_ends_at ? row.subscription_ends_at.slice(0, 10) : '—' }}
                    </template>
                </el-table-column>
                <el-table-column label="企微" width="100">
                    <template #default="{ row }">
                        <el-tag v-if="row.enterprise_wechat_id" type="success" size="small">已授权</el-tag>
                        <el-tag v-else type="info" size="small">未授权</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="100">
                    <template #default="{ row }">
                        <el-tag v-if="row.is_banned" type="danger" size="small">已禁用</el-tag>
                        <el-tag v-else type="success" size="small">正常</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="88" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="goEdit(row.id)">编辑</el-button>
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
    </AdminPageShell>
</template>

<style scoped>
.oc-u-alert {
    margin-bottom: 12px;
}
</style>

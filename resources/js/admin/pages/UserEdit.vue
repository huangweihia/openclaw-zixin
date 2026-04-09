<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { enumOptions } from '../constants/labels';

const userRoleOpts = enumOptions('userRole');
const subscriptionPlanOpts = enumOptions('subscriptionPlan');

const route = useRoute();
const router = useRouter();

const user = ref(null);
const currentUserId = ref(null);
const loadErr = ref('');
const flash = ref('');
const flashErr = ref('');
const saving = ref(false);

const name = ref('');
const email = ref('');
const role = ref('user');

const vipPlan = ref('monthly');
const vipDays = ref(30);
/** @type {import('vue').Ref<'grant_vip' | 'grant_svip' | 'clear'>} */
const membershipAction = ref('grant_vip');

const isSelf = computed(() => user.value && currentUserId.value === user.value.id);

async function load() {
    loadErr.value = '';
    flash.value = '';
    flashErr.value = '';
    try {
        const [uRes, meRes] = await Promise.all([
            axios.get(`/api/admin/users/${route.params.id}`),
            axios.get('/api/admin/me'),
        ]);
        user.value = uRes.data.user;
        currentUserId.value = meRes.data.user.id;
        name.value = user.value.name;
        email.value = user.value.email;
        role.value = user.value.role;
    } catch {
        loadErr.value = '无法加载用户';
    }
}

onMounted(load);

function apiErr(e) {
    return e.response?.data?.message || '操作失败';
}

async function saveProfile() {
    saving.value = true;
    flash.value = '';
    flashErr.value = '';
    try {
        const { data } = await axios.put(`/api/admin/users/${route.params.id}`, {
            name: name.value,
            email: email.value,
            role: role.value,
        });
        user.value = data.user;
        flash.value = data.message || '已保存';
    } catch (e) {
        flashErr.value = apiErr(e);
    } finally {
        saving.value = false;
    }
}

async function doDisable() {
    if (!confirm('确定禁用该用户？')) {
        return;
    }
    flash.value = '';
    flashErr.value = '';
    try {
        const { data } = await axios.post(`/api/admin/users/${route.params.id}/disable`);
        user.value = data.user;
        flash.value = data.message;
    } catch (e) {
        flashErr.value = apiErr(e);
    }
}

async function doEnable() {
    flash.value = '';
    flashErr.value = '';
    try {
        const { data } = await axios.post(`/api/admin/users/${route.params.id}/enable`);
        user.value = data.user;
        flash.value = data.message;
    } catch (e) {
        flashErr.value = apiErr(e);
    }
}

async function clearWecom() {
    if (!confirm('确定清除该用户的企业微信绑定？用户需重新授权。')) {
        return;
    }
    flash.value = '';
    flashErr.value = '';
    try {
        const { data } = await axios.post(`/api/admin/users/${route.params.id}/clear-enterprise-wechat`);
        user.value = data.user;
        flash.value = data.message;
    } catch (e) {
        flashErr.value = apiErr(e);
    }
}

async function doMembership() {
    flash.value = '';
    flashErr.value = '';
    if (membershipAction.value === 'clear') {
        if (!confirm('确定清除该用户的会员身份与到期时间？（管理员账号仅清除到期时间）')) {
            return;
        }
    }
    try {
        const payload =
            membershipAction.value === 'clear'
                ? { action: 'clear' }
                : {
                      action: membershipAction.value,
                      plan: vipPlan.value,
                      days: Number(vipDays.value),
                  };
        const { data } = await axios.post(`/api/admin/users/${route.params.id}/membership`, payload);
        user.value = data.user;
        flash.value = data.message;
    } catch (e) {
        flashErr.value = apiErr(e);
    }
}
</script>

<template>
    <div>
        <p class="back">
            <button type="button" class="link-btn" @click="router.push({ name: 'users' })">← 返回列表</button>
        </p>
        <h1 class="page-title">编辑用户</h1>
        <p v-if="loadErr" class="msg-err">{{ loadErr }}</p>
        <template v-else-if="user">
            <p v-if="flash" class="msg-ok">{{ flash }}</p>
            <p v-if="flashErr" class="msg-err">{{ flashErr }}</p>

            <section class="card">
                <h2 class="card__title">基本信息</h2>
                <label class="field">
                    <span>昵称</span>
                    <input v-model="name" type="text" />
                </label>
                <label class="field">
                    <span>邮箱</span>
                    <input v-model="email" type="email" />
                </label>
                <label class="field">
                    <span>角色</span>
                    <select v-model="role">
                        <option v-for="o in userRoleOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <button type="button" class="btn primary" :disabled="saving" @click="saveProfile">
                    保存
                </button>
            </section>

            <section v-if="user.enterprise_wechat" class="card">
                <h2 class="card__title">企业微信</h2>
                <p class="hint">
                    状态：{{ user.enterprise_wechat.bound ? user.enterprise_wechat.masked || '已绑定' : '未绑定' }}
                </p>
                <button type="button" class="btn danger" :disabled="!user.enterprise_wechat.bound" @click="clearWecom">
                    清除企业微信绑定
                </button>
            </section>

            <section class="card">
                <h2 class="card__title">会员权益（VIP / SVIP / 清除）</h2>
                <p class="hint">当前角色：{{ user.role }}；到期：{{ user.subscription_ends_at || '—' }}</p>
                <label class="field">
                    <span>操作</span>
                    <select v-model="membershipAction">
                        <option value="grant_vip">开通 / 续期 VIP</option>
                        <option value="grant_svip">开通 / 续期 SVIP</option>
                        <option value="clear">清除会员权益</option>
                    </select>
                </label>
                <div v-if="membershipAction !== 'clear'" class="row">
                    <label class="field inline">
                        <span>套餐</span>
                        <select v-model="vipPlan">
                            <option v-for="o in subscriptionPlanOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                        </select>
                    </label>
                    <label class="field inline">
                        <span>天数</span>
                        <input v-model.number="vipDays" type="number" min="1" max="3650" />
                    </label>
                </div>
                <button type="button" class="btn primary" @click="doMembership">执行</button>
            </section>

            <section class="card">
                <h2 class="card__title">账号状态</h2>
                <p class="hint">
                    当前：{{ user.is_banned ? '已禁用' : '正常' }}
                    <template v-if="isSelf">（当前登录账号）</template>
                </p>
                <div class="row">
                    <button
                        type="button"
                        class="btn danger"
                        :disabled="isSelf || user.role === 'admin'"
                        @click="doDisable"
                    >
                        禁用
                    </button>
                    <button type="button" class="btn" :disabled="!user.is_banned" @click="doEnable">
                        解除禁用
                    </button>
                </div>
                <p v-if="user.role === 'admin'" class="hint">管理员需先改角色为非 admin 才可禁用。</p>
            </section>
        </template>
    </div>
</template>

<style scoped>
.back {
    margin: 0 0 0.75rem;
}
.link-btn {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
    font-size: 0.9rem;
}
.page-title {
    margin: 0 0 1.25rem;
    font-size: 1.5rem;
}
.msg-err {
    color: #b91c1c;
}
.msg-ok {
    color: #166534;
}
.card {
    background: #fff;
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
}
.card__title {
    margin: 0 0 1rem;
    font-size: 1.05rem;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}
.field.inline {
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0;
}
.field span {
    min-width: 3rem;
    color: #64748b;
}
.field input,
.field select {
    max-width: 360px;
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.hint {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0 0 0.75rem;
}
.row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.75rem;
}
.btn {
    padding: 0.45rem 0.9rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-size: 0.875rem;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.btn.danger {
    border-color: #fecaca;
    color: #b91c1c;
}
.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPageShell from '../components/AdminPageShell.vue';
import { toast } from '../../frontend/utils/toast';

const LS_TOKEN_KEY = 'oc_personality_quiz_admin_token';

const token = ref('');
const loading = ref(false);
const err = ref('');

const enabled = ref(true);
const threshold = ref(60);
const dimCount = ref(null);

const manageUrl = computed(() => {
    const t = (token.value || '').trim();
    const base = `${window.location.origin}/personality-quiz/manage`;
    if (!t) return base;
    return `${base}?token=${encodeURIComponent(t)}`;
});

function headers() {
    return {
        'X-Personality-Quiz-Admin-Token': (token.value || '').trim(),
        Accept: 'application/json',
    };
}

async function load() {
    err.value = '';
    const t = (token.value || '').trim();
    if (!t) {
        err.value = '请先填写管理 Token（PERSONALITY_QUIZ_ADMIN_TOKEN）。';
        return;
    }
    loading.value = true;
    try {
        const { data } = await axios.get('/api/personality-quiz/admin/bootstrap', { headers: headers() });
        const s = data.settings || {};
        enabled.value = String(s.enabled ?? '1') !== '0';
        threshold.value = Number(s.low_match_threshold ?? 60);
        dimCount.value = data.active_dimension_count ?? null;
        localStorage.setItem(LS_TOKEN_KEY, t);
    } catch (e) {
        err.value = e.response?.data?.message || e.message || '加载失败';
    } finally {
        loading.value = false;
    }
}

async function save() {
    err.value = '';
    const t = (token.value || '').trim();
    if (!t) {
        err.value = '请先填写管理 Token。';
        return;
    }
    loading.value = true;
    try {
        await axios.put('/api/personality-quiz/admin/settings', { key: 'enabled', value: enabled.value ? '1' : '0' }, { headers: headers() });
        await axios.put('/api/personality-quiz/admin/settings', { key: 'low_match_threshold', value: String(threshold.value ?? '') }, { headers: headers() });
        toast('已保存');
        localStorage.setItem(LS_TOKEN_KEY, t);
        await load();
    } catch (e) {
        err.value = e.response?.data?.message || e.message || '保存失败';
    } finally {
        loading.value = false;
    }
}

function copyManageUrl() {
    navigator.clipboard?.writeText(manageUrl.value);
    toast('已复制管理页链接');
}

onMounted(() => {
    try {
        token.value = localStorage.getItem(LS_TOKEN_KEY) || '';
    } catch {}
    if ((token.value || '').trim()) {
        load();
    }
});
</script>

<template>
    <AdminPageShell title="SBTI">
        <div class="card">
            <div class="card__body space-y-4">
                <div class="text-slate-600">
                    该功能前台无需登录即可首次体验；游客完成后会被限制再次参与（注册登录后可再玩）。
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="font-semibold">管理 Token</div>
                        <input v-model="token" class="input w-full mono" placeholder="PERSONALITY_QUIZ_ADMIN_TOKEN" />
                        <div class="flex flex-wrap gap-2">
                            <button class="btn primary" :disabled="loading" @click="load">加载配置</button>
                            <a class="btn" :href="manageUrl" target="_blank" rel="noreferrer">打开无登录管理页</a>
                            <button class="btn" type="button" @click="copyManageUrl">复制管理页链接</button>
                        </div>
                        <div v-if="dimCount != null" class="text-xs text-slate-500">
                            当前启用维度数：<span class="mono">{{ dimCount }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="font-semibold">功能开关</div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" v-model="enabled" />
                            <span>启用（启用时首页展示入口）</span>
                        </label>

                        <div class="space-y-1">
                            <div class="text-sm text-slate-600">低匹配阈值 low_match_threshold（0–100）</div>
                            <input type="number" min="0" max="100" v-model.number="threshold" class="input w-40" />
                        </div>

                        <div class="flex gap-2">
                            <button class="btn primary" :disabled="loading" @click="save">保存</button>
                        </div>
                    </div>
                </div>

                <div v-if="err" class="err">{{ err }}</div>
                <div v-if="loading" class="text-slate-500 text-sm">处理中…</div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; }
.input {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 0.55rem 0.75rem;
    outline: none;
}
.input:focus { border-color: #1677ff; }
</style>


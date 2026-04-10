<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref('');
const editing = ref(null);
const form = ref({ name: '', key: '', value: '', description: '' });
const testTo = ref('');
const testSubject = ref('');
const testMsg = ref('');
const testErr = ref('');
const testLoading = ref(false);

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/email-settings');
        rows.value = data.settings ?? [];
    } catch {
        err.value = '加载失败';
    }
}

async function save() {
    msg.value = '';
    err.value = '';
    try {
        const body = {
            name: form.value.name,
            value: form.value.value,
            description: form.value.description || '',
        };
        if (mode.value === 'create') await axios.post('/api/admin/email-settings', body);
        else await axios.put(`/api/admin/email-settings/${editing.value.id}`, body);
        msg.value = '已保存';
        mode.value = '';
        editing.value = null;
        await load();
    } catch {
        err.value = '保存失败';
    }
}

function closeFormModal() {
    mode.value = '';
    err.value = '';
    editing.value = null;
}

function openCreate() {
    err.value = '';
    mode.value = 'create';
    editing.value = null;
    form.value = { name: '', key: '', value: '', description: '' };
}

function openEdit(r) {
    err.value = '';
    mode.value = 'edit';
    editing.value = r;
    form.value = { name: r.name, key: r.key, value: r.value, description: r.description || '' };
}

async function removeRow(r) {
    if (!confirm(`删除配置「${r.key}」？`)) return;
    await axios.delete(`/api/admin/email-settings/${r.id}`);
    msg.value = '已删除';
    await load();
}

onMounted(load);

async function sendTest() {
    testMsg.value = '';
    testErr.value = '';
    testLoading.value = true;
    try {
        const { data } = await axios.post('/api/admin/email-settings/test-send', {
            to: testTo.value.trim(),
            subject: testSubject.value.trim() || undefined,
        });
        testMsg.value = data.message || '已发送';
    } catch (e) {
        testErr.value = e.response?.data?.message || '发送失败';
    } finally {
        testLoading.value = false;
    }
}
</script>

<template>
    <AdminPageShell title="邮件配置" lead="SMTP/订阅发送相关参数（后台可维护键值），支持测试发信。">
        <template #actions>
            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>
        </template>
        <p class="pg__lead">
            表 <code>email_settings</code>（键值对）。连通性测试使用环境变量中的 <code>MAIL_*</code>，与注册发信等共用 Laravel Mail。
            订阅推荐键：<code>mail_sub_batch_size</code>（每批发送数）、<code>mail_sub_daily_cap</code>（每日最大发送数，0 不限）。
        </p>
        <div class="test-card">
            <h2 class="test-card__title">发送测试邮件</h2>
            <div class="test-row">
                <label class="fld-inline">
                    <span>收件邮箱</span>
                    <input v-model="testTo" type="email" placeholder="you@example.com" />
                </label>
                <label class="fld-inline">
                    <span>主题（可选）</span>
                    <input v-model="testSubject" type="text" placeholder="留空则使用默认主题" />
                </label>
                <button
                    type="button"
                    class="btn btn--pri"
                    :disabled="testLoading || !testTo.trim()"
                    @click="sendTest"
                >
                    {{ testLoading ? '发送中…' : '发送测试' }}
                </button>
            </div>
            <p v-if="testMsg" class="ok">{{ testMsg }}</p>
            <p v-if="testErr" class="bad">{{ testErr }}</p>
        </div>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="bad">{{ err }}</p>
        <AdminCard>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>键</th>
                        <th>名称</th>
                        <th>值摘要</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td class="mono">{{ r.key }}</td>
                        <td>{{ r.name }}</td>
                        <td class="clip">{{ (r.value || '').slice(0, 48) }}{{ (r.value || '').length > 48 ? '…' : '' }}</td>
                        <td class="act">
                            <button type="button" class="lnk" @click="openEdit(r)">编辑</button>
                            <button type="button" class="lnk lnk--d" @click="removeRow(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无</p>
        </AdminCard>
        <div v-if="mode" class="modal" @click.self="closeFormModal">
            <div class="modal__box" @click.stop>
                <h2>{{ mode === 'create' ? '新建' : '编辑' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld"><span>名称</span><input v-model="form.name" type="text" /></label>
                <div v-if="mode === 'create'" class="fld fld--note">
                    <span>配置键</span>
                    <span class="fld-hint">保存后由系统根据名称自动生成。</span>
                </div>
                <label v-else class="fld">
                    <span>配置键</span>
                    <p class="fld-readonly mono">{{ form.key }}</p>
                </label>
                <label class="fld"><span>配置值 value</span><textarea v-model="form.value" rows="5" /></label>
                <label class="fld"><span>描述</span><input v-model="form.description" type="text" /></label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeFormModal">取消</button>
                    <button type="button" class="btn btn--pri" @click="save">保存</button>
                </div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.pg__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}
.pg__title {
    margin: 0;
    font-size: 1.5rem;
}
.pg__lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
}
.ok {
    color: #166534;
}
.bad {
    color: #b91c1c;
}
.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: auto;
}
.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.tbl th,
.tbl td {
    padding: 0.5rem 0.65rem;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
}
.tbl th {
    background: #f8fafc;
    font-weight: 600;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
}
.clip {
    max-width: 220px;
    color: #64748b;
}
.act {
    display: flex;
    gap: 0.5rem;
}
.lnk {
    border: none;
    background: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
}
.lnk--d {
    color: #b91c1c;
}
.empty {
    padding: 1rem;
    color: #94a3b8;
    margin: 0;
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
.fld {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
}
.fld input,
.fld textarea {
    padding: 0.45rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.fld--note {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}
.fld-hint {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.45;
}
.fld-readonly {
    margin: 0;
    padding: 0.45rem 0.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #0f172a;
}
.modal__btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.btn {
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.btn--pri {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.test-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
}
.test-card__title {
    margin: 0 0 0.75rem;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}
.test-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem 1rem;
    align-items: flex-end;
}
.fld-inline {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.82rem;
}
.fld-inline input {
    min-width: 200px;
    padding: 0.45rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.pg__lead code {
    font-size: 0.82em;
    background: #e2e8f0;
    padding: 0.08rem 0.3rem;
    border-radius: 4px;
}
</style>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { enumOptions } from '../constants/labels';
import { toast } from '../../frontend/utils/toast';
import AdminPageShell from '../components/AdminPageShell.vue';

const giftRoleOpts = enumOptions('userRole').filter((o) => o.value === 'vip' || o.value === 'svip');

const err = ref('');
const msg = ref('');
const uploadingLogo = ref(false);
const logoInputRef = ref(null);
const form = ref({
    site_name: '',
    site_slogan: '',
    site_description: '',
    site_logo_url: '',
    contact_email: '',
    contact_wechat: '',
    footer_notice: '',
    analytics_note: '',
    register_gift_enabled: '0',
    register_gift_role: 'vip',
    register_gift_days: '0',
    register_points_bonus: '100',
    pricing_vip_deadline: '',
    pricing_svip_deadline: '',
    pricing_vip_seats: '200',
    pricing_svip_seats: '50',
    pricing_vip_promo: '',
    pricing_svip_promo: '',
    mail_batch_enabled: '1',
    mail_batch_start_hour: '9',
    mail_batch_end_hour: '22',
});

const hint = ref('');

const logoPreviewSrc = computed(() => {
    const raw = (form.value.site_logo_url || '').trim();
    if (!raw) return null;
    if (/^https?:\/\//i.test(raw)) return raw;
    const base = window.location.origin || '';
    const path = raw.startsWith('/') ? raw : `/${raw}`;
    return `${base}${path}`;
});

async function load() {
    err.value = '';
    hint.value = '';
    try {
        const { data } = await axios.get('/api/admin/settings');
        hint.value = data.hint || '';
        const s = data.settings ?? {};
        form.value = {
            site_name: s.site_name ?? '',
            site_slogan: s.site_slogan ?? '',
            site_description: s.site_description ?? '',
            site_logo_url: s.site_logo_url ?? '',
            contact_email: s.contact_email ?? '',
            contact_wechat: s.contact_wechat ?? '',
            footer_notice: s.footer_notice ?? '',
            analytics_note: s.analytics_note ?? '',
            register_gift_enabled: s.register_gift_enabled ?? '0',
            register_gift_role: s.register_gift_role ?? 'vip',
            register_gift_days: String(s.register_gift_days ?? '0'),
            register_points_bonus: String(s.register_points_bonus ?? '100'),
            pricing_vip_deadline: s.pricing_vip_deadline ?? '',
            pricing_svip_deadline: s.pricing_svip_deadline ?? '',
            pricing_vip_seats: String(s.pricing_vip_seats ?? '200'),
            pricing_svip_seats: String(s.pricing_svip_seats ?? '50'),
            pricing_vip_promo: s.pricing_vip_promo ?? '',
            pricing_svip_promo: s.pricing_svip_promo ?? '',
            mail_batch_enabled: s.mail_batch_enabled ?? '1',
            mail_batch_start_hour: String(s.mail_batch_start_hour ?? '9'),
            mail_batch_end_hour: String(s.mail_batch_end_hour ?? '22'),
        };
    } catch (e) {
        const st = e.response?.status;
        const detail = e.response?.data?.message || e.message || '';
        err.value = st
            ? `加载失败（HTTP ${st}）${detail ? `：${detail}` : ''}`
            : `加载失败：${detail || '网络或服务异常'}`;
    }
}

onMounted(load);

function chooseLogoFile() {
    logoInputRef.value?.click();
}

async function onLogoFileChange(event) {
    const file = event?.target?.files?.[0];
    if (!file) return;
    err.value = '';
    msg.value = '';
    uploadingLogo.value = true;
    try {
        const formData = new FormData();
        formData.append('image', file);
        const { data } = await axios.post('/api/admin/uploads/image', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (data?.url) {
            form.value.site_logo_url = data.url;
            msg.value = 'Logo 上传成功，记得点击“保存全部设置”生效';
        } else {
            err.value = '上传失败：接口未返回图片地址';
        }
    } catch (e) {
        err.value = e.response?.data?.message || 'Logo 上传失败';
    } finally {
        uploadingLogo.value = false;
        if (event?.target) event.target.value = '';
    }
}

async function save() {
    err.value = '';
    msg.value = '';
    try {
        const { data } = await axios.put('/api/admin/settings', { settings: { ...form.value } });
        const s = data.settings ?? {};
        form.value = {
            site_name: s.site_name ?? '',
            site_slogan: s.site_slogan ?? '',
            site_description: s.site_description ?? '',
            site_logo_url: s.site_logo_url ?? '',
            contact_email: s.contact_email ?? '',
            contact_wechat: s.contact_wechat ?? '',
            footer_notice: s.footer_notice ?? '',
            analytics_note: s.analytics_note ?? '',
            register_gift_enabled: s.register_gift_enabled ?? '0',
            register_gift_role: s.register_gift_role ?? 'vip',
            register_gift_days: String(s.register_gift_days ?? '0'),
            register_points_bonus: String(s.register_points_bonus ?? '100'),
            pricing_vip_deadline: s.pricing_vip_deadline ?? '',
            pricing_svip_deadline: s.pricing_svip_deadline ?? '',
            pricing_vip_seats: String(s.pricing_vip_seats ?? '200'),
            pricing_svip_seats: String(s.pricing_svip_seats ?? '50'),
            pricing_vip_promo: s.pricing_vip_promo ?? '',
            pricing_svip_promo: s.pricing_svip_promo ?? '',
            mail_batch_enabled: s.mail_batch_enabled ?? '1',
            mail_batch_start_hour: String(s.mail_batch_start_hour ?? '9'),
            mail_batch_end_hour: String(s.mail_batch_end_hour ?? '22'),
        };
        msg.value = '已保存';
        toast.success('系统与站点设置已保存', 1600);
    } catch (e) {
        const message = e.response?.data?.message || '保存失败';
        err.value = message;
        toast.error(message, 2200);
    }
}
</script>

<template>
    <AdminPageShell
        title="系统与站点"
        lead="统一写入 site_settings 表。注册赠送会员已在 RegisterController 闭环生效；邮件时间窗供定时任务/队列读取（可在 app/Console/Kernel.php 调度里判断当前小时）。"
    >
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="hint" class="hint-banner">{{ hint }}</p>
        <p v-if="err" class="err">{{ err }}</p>

        <div class="card">
            <h2 class="sec-title">站点展示</h2>
            <label class="field">
                <span>站点名称</span>
                <input v-model="form.site_name" type="text" />
            </label>
            <label class="field">
                <span>站点标语（首页 Hero 等）</span>
                <textarea v-model="form.site_slogan" rows="2" />
            </label>
            <label class="field">
                <span>站点简介</span>
                <textarea v-model="form.site_description" rows="3" />
            </label>
            <label class="field">
                <span>站点 Logo 地址</span>
                <input v-model="form.site_logo_url" type="text" placeholder="留空则用默认 favicon；可填完整 URL 或 /storage/... 相对路径" maxlength="500" />
            </label>
            <div class="logo-upload-row">
                <input ref="logoInputRef" type="file" accept="image/jpeg,image/png,image/gif,image/webp,image/jpg" class="logo-file-input" @change="onLogoFileChange" />
                <button type="button" class="btn" :disabled="uploadingLogo" @click="chooseLogoFile">
                    {{ uploadingLogo ? '上传中...' : '上传 Logo 图片' }}
                </button>
            </div>
            <p class="hint">前台顶栏、页脚与首页 Hero 左侧图标均读取该地址（经 <code>SiteViewComposer::publicAssetUrl</code> 解析）。</p>
            <div v-if="logoPreviewSrc" class="logo-preview">
                <span class="logo-preview__label">当前 Logo 预览</span>
                <img :src="logoPreviewSrc" alt="Logo 预览" class="logo-preview__img" loading="lazy" />
            </div>
            <label class="field">
                <span>联系邮箱</span>
                <input v-model="form.contact_email" type="email" />
            </label>
            <label class="field">
                <span>联系微信（展示在页脚）</span>
                <input v-model="form.contact_wechat" type="text" maxlength="120" />
            </label>
            <label class="field">
                <span>页脚提示 / HTML</span>
                <textarea v-model="form.footer_notice" rows="4" />
            </label>
            <p class="hint">
                富文本编辑器：Vue 侧可选用 <strong>Quill</strong>、<strong>Tiptap</strong>、<strong>CKEditor 5</strong> 等（<code>npm</code>
                安装），输出 HTML 写入本字段即可；与 Blade 侧 TinyMCE 可并存。
            </p>
            <label class="field">
                <span>统计 / 运营备注（纯记录）</span>
                <textarea v-model="form.analytics_note" rows="3" />
            </label>
        </div>

        <div class="card">
            <h2 class="sec-title">注册与会员（业务闭环）</h2>
            <p class="hint">新用户邮箱注册成功后，若开启赠送：将角色设为 VIP/SVIP 并写入 <code>subscription_ends_at</code>。</p>
            <label class="check">
                <input v-model="form.register_gift_enabled" type="checkbox" true-value="1" false-value="0" />
                开启注册赠送会员
            </label>
            <label class="field">
                <span>赠送角色</span>
                <select v-model="form.register_gift_role">
                    <option v-for="o in giftRoleOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                </select>
            </label>
            <label class="field">
                <span>赠送天数（0 表示不赠送）</span>
                <input v-model="form.register_gift_days" type="number" min="0" max="3650" />
            </label>
            <label class="field">
                <span>注册赠送积分（0 关闭；写入流水表 points）</span>
                <input v-model="form.register_points_bonus" type="number" min="0" max="999999" />
            </label>
        </div>

        <div class="card">
            <h2 class="sec-title">价格营销（首页 / VIP 页）</h2>
            <p class="hint">
                截止时间为 <strong>未来时间点</strong>（建议 ISO8601，如 <code>2026-12-31T23:59:59+08:00</code>），前台显示倒计时；名额为数字，用于「仅剩 N 席」文案。
            </p>
            <div class="row2">
                <label class="field">
                    <span>VIP 优惠截止时间</span>
                    <input v-model="form.pricing_vip_deadline" type="text" placeholder="留空则不显示倒计时" />
                </label>
                <label class="field">
                    <span>SVIP 优惠截止时间</span>
                    <input v-model="form.pricing_svip_deadline" type="text" placeholder="留空则不显示倒计时" />
                </label>
            </div>
            <div class="row2">
                <label class="field">
                    <span>VIP 剩余名额（数字）</span>
                    <input v-model="form.pricing_vip_seats" type="number" min="0" />
                </label>
                <label class="field">
                    <span>SVIP 剩余名额（数字）</span>
                    <input v-model="form.pricing_svip_seats" type="number" min="0" />
                </label>
            </div>
            <div class="row2">
                <label class="field">
                    <span>VIP 营销副标题（如限时特惠）</span>
                    <input v-model="form.pricing_vip_promo" type="text" maxlength="120" />
                </label>
                <label class="field">
                    <span>SVIP 营销副标题（如早鸟价）</span>
                    <input v-model="form.pricing_svip_promo" type="text" maxlength="120" />
                </label>
            </div>
        </div>

        <div class="card">
            <h2 class="sec-title">邮件批处理时间窗</h2>
            <p class="hint">整点 0–23。调度任务发送营销/汇总邮件前应读取这两项，避免夜间打扰（需自行在 Kernel 或队列中接入）。</p>
            <label class="check">
                <input v-model="form.mail_batch_enabled" type="checkbox" true-value="1" false-value="0" />
                启用时间窗限制（关闭则由任务自行决定，不读此配置）
            </label>
            <div class="row2">
                <label class="field">
                    <span>允许开始小时</span>
                    <input v-model="form.mail_batch_start_hour" type="number" min="0" max="23" />
                </label>
                <label class="field">
                    <span>允许结束小时</span>
                    <input v-model="form.mail_batch_end_hour" type="number" min="0" max="23" />
                </label>
            </div>
        </div>

        <button type="button" class="btn primary" @click="save">保存全部设置</button>
    </AdminPageShell>
</template>

<style scoped>
.page-title {
    margin: 0 0 0.75rem;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1.25rem;
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.55;
}
.lead code {
    font-size: 0.85em;
    background: #e2e8f0;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.hint-banner {
    margin: 0 0 0.75rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.85rem;
    color: #92400e;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    line-height: 1.45;
}
.card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 1.15rem 1.25rem 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}
.sec-title {
    margin: 0 0 0.85rem;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}
.hint {
    margin: 0 0 0.85rem;
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.5;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.85rem;
    font-size: 0.88rem;
}
.field input,
.field textarea,
.field select {
    padding: 0.5rem 0.6rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.85rem;
    font-size: 0.88rem;
}
.row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 520px) {
    .row2 {
        grid-template-columns: 1fr;
    }
}
.btn {
    padding: 0.55rem 1.1rem;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.logo-upload-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin: -0.2rem 0 0.85rem;
}
.logo-file-input {
    display: none;
}
.logo-preview {
    display: inline-flex;
    flex-direction: column;
    gap: 0.35rem;
    margin: 0 0 0.85rem;
    font-size: 0.8rem;
    color: #64748b;
}
.logo-preview__label {
    font-weight: 500;
}
.logo-preview__img {
    width: 72px;
    height: 72px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    object-fit: contain;
    background: #f8fafc;
    padding: 6px;
}
</style>

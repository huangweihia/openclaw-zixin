import '../bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import { createAdminRouter } from './router';
import ElementPlus from 'element-plus';
import { ElMessage } from 'element-plus';
import zhCn from 'element-plus/es/locale/lang/zh-cn';
import 'element-plus/dist/index.css';
import './admin-theme.css';

const base = typeof window.__ADMIN_ROUTER_BASE__ === 'string' && window.__ADMIN_ROUTER_BASE__ !== ''
    ? window.__ADMIN_ROUTER_BASE__
    : '/admin/';

const app = createApp(App);
app.use(ElementPlus, { locale: zhCn, size: 'default' });
app.use(createAdminRouter(base));

window.axios.interceptors.response.use(
    (res) => {
        const method = String(res?.config?.method || '').toLowerCase();
        const msg = res?.data?.message;
        if (msg && ['post', 'put', 'patch', 'delete'].includes(method)) {
            ElMessage.success(String(msg));
        }
        return res;
    },
    (error) => {
        const msg = error?.response?.data?.message;
        if (msg) {
            ElMessage.error(String(msg));
        }
        return Promise.reject(error);
    },
);

app.mount('#admin-app');

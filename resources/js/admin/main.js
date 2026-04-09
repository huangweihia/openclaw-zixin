import '../bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import { createAdminRouter } from './router';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';

const base = typeof window.__ADMIN_ROUTER_BASE__ === 'string' && window.__ADMIN_ROUTER_BASE__ !== ''
    ? window.__ADMIN_ROUTER_BASE__
    : '/admin/';

const app = createApp(App);
app.use(ElementPlus);
app.use(createAdminRouter(base));
app.mount('#admin-app');

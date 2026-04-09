import '../bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';

// 页面组件
import Home from '../Pages/Home.vue';
import ArticlesIndex from '../Pages/ArticlesIndex.vue';
import ArticleShow from '../Pages/ArticleShow.vue';
import TestComponents from '../Pages/TestComponents.vue';

// 全局组件
import Toast from './components/Toast.vue';
import Skeleton from './components/Skeleton.vue';

// 指令
import loading from './directives/loading';

// Toast 工具
import { toast } from './utils/toast';

// 路由配置
const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { title: '首页' },
    },
    {
        path: '/articles',
        name: 'articles.index',
        component: ArticlesIndex,
        meta: { title: '文章列表' },
    },
    {
        path: '/articles/:slug',
        name: 'articles.show',
        component: ArticleShow,
        meta: { title: '文章详情' },
    },
    {
        path: '/test',
        name: 'test',
        component: TestComponents,
        meta: { title: '组件测试' },
    },
];

// 创建路由
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// 路由守卫 - 设置页面标题
router.beforeEach((to, from, next) => {
    const title = to.meta?.title;
    if (title) {
        document.title = `${title} - OpenClaw 智信`;
    }
    next();
});

// 创建应用
const app = createApp(App);
app.use(router);

// 注册全局组件
app.component('Toast', Toast);
app.component('Skeleton', Skeleton);

// 注册指令
app.directive('loading', loading);

// 挂载应用
app.mount('#app');

// 导出 toast 到全局
window.toast = toast;

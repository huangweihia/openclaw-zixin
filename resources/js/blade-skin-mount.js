import './bootstrap';
import { createApp, h } from 'vue';
import SkinSwitcher from './Components/SkinSwitcher.vue';

function mountSkinSwitcher() {
    const el = document.getElementById('blade-skin-switcher');
    if (!el) {
        return;
    }
    createApp({
        render: () => h(SkinSwitcher, { variant: 'navbar' }),
    }).mount(el);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountSkinSwitcher);
} else {
    mountSkinSwitcher();
}

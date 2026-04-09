<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'info', // success, error, warning, info
    },
    duration: {
        type: Number,
        default: 3000,
    },
});

const emit = defineEmits(['update:modelValue', 'close']);

const visible = ref(props.modelValue);
const timer = ref(null);

// 图标映射
const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️',
};

// 类型样式映射
const typeStyles = {
    success: { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-800' },
    error: { bg: 'bg-red-50', border: 'border-red-500', text: 'text-red-800' },
    warning: { bg: 'bg-orange-50', border: 'border-orange-500', text: 'text-orange-800' },
    info: { bg: 'bg-blue-50', border: 'border-blue-500', text: 'text-blue-800' },
};

// 自动关闭
watch(() => props.modelValue, (newVal) => {
    visible.value = newVal;
    if (newVal && props.duration > 0) {
        startTimer();
    }
});

function startTimer() {
    if (timer.value) clearTimeout(timer.value);
    timer.value = setTimeout(() => {
        close();
    }, props.duration);
}

function close() {
    visible.value = false;
    emit('update:modelValue', false);
    emit('close');
}

// 鼠标悬停时暂停计时
function onMouseEnter() {
    if (timer.value) clearTimeout(timer.value);
}

function onMouseLeave() {
    if (visible.value && props.duration > 0) {
        startTimer();
    }
}
</script>

<template>
    <Teleport to="body">
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-x-full opacity-0"
            enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-x-0 opacity-100"
            leave-to-class="transform translate-x-full opacity-0"
        >
            <div
                v-if="visible"
                class="fixed top-4 right-4 z-50 flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg border-l-4"
                :class="[typeStyles[type].bg, typeStyles[type].border]"
                @mouseenter="onMouseEnter"
                @mouseleave="onMouseLeave"
            >
                <span class="text-2xl">{{ icons[type] }}</span>
                <span :class="['font-medium', typeStyles[type].text]">{{ message }}</span>
                <button
                    @click="close"
                    class="ml-2 text-gray-400 hover:text-gray-600 transition"
                    type="button"
                >
                    ✕
                </button>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>
/* Toast 样式 */
</style>

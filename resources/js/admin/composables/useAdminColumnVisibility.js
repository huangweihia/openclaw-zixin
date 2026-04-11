import { ref, watch } from 'vue';

const LS_PREFIX = 'oc-admin-cols:';

/**
 * 列表列可见性（localStorage 持久化，按 pageKey 隔离）。
 * @param {string} pageKey 唯一键，如 admin:projects:list
 * @param {Array<{ key: string, label: string, field?: string, default?: boolean }>} definitions
 *        default === false 表示默认不勾选；省略则默认勾选。
 */
export function useAdminColumnVisibility(pageKey, definitions) {
    const storageKey = `${LS_PREFIX}${pageKey}`;
    const validKeys = new Set(definitions.map((d) => d.key));

    function defaultSelected() {
        return definitions.filter((d) => d.default !== false).map((d) => d.key);
    }

    function readStored() {
        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) {
                return null;
            }
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return null;
            }
            const out = parsed.filter((k) => validKeys.has(k));
            return out.length ? out : null;
        } catch {
            return null;
        }
    }

    const selectedKeys = ref(readStored() ?? defaultSelected());

    watch(
        selectedKeys,
        (v) => {
            try {
                localStorage.setItem(storageKey, JSON.stringify(v));
            } catch {
                /* quota / private mode */
            }
        },
        { deep: true },
    );

    /** @param {string} key */
    function show(key) {
        return selectedKeys.value.includes(key);
    }

    function selectAll() {
        selectedKeys.value = definitions.map((d) => d.key);
    }

    function resetDefault() {
        selectedKeys.value = defaultSelected();
    }

    return {
        definitions,
        selectedKeys,
        show,
        selectAll,
        resetDefault,
    };
}

<x-filament-panels::page>
    <div class="space-y-6">
        <div
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">无登录全功能管理端</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                与旧版 Vue 管理页一致：维度、题目、选项、类型等可在独立页面中批量维护（需配置
                <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-gray-800">PERSONALITY_QUIZ_ADMIN_TOKEN</code>）。
            </p>
            <div class="mt-4">
                <x-filament::button tag="a" :href="$this->manageUrl" target="_blank" rel="noopener noreferrer" color="primary">
                    打开测评管理页
                </x-filament::button>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Filament 分表入口</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                适合在后台内快速改少量数据；复杂编排建议用上方管理端。
            </p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->filamentLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        class="rounded-xl border border-gray-200 p-4 transition hover:border-primary-400 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-primary-500 dark:hover:bg-gray-800/80"
                    >
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $link['label'] }}</div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $link['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">
        <div
            class="grid gap-4 sm:grid-cols-3"
        >
            <div
                class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/40"
            >
                <div class="text-sm font-medium text-amber-900 dark:text-amber-100">待审动态</div>
                <div class="mt-1 text-3xl font-semibold tabular-nums text-amber-950 dark:text-amber-50">
                    {{ $this->pendingPostsCount }}
                </div>
            </div>
            <div
                class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/50 dark:bg-sky-950/40"
            >
                <div class="text-sm font-medium text-sky-900 dark:text-sky-100">待审发布审计</div>
                <div class="mt-1 text-3xl font-semibold tabular-nums text-sky-950 dark:text-sky-50">
                    {{ $this->pendingPublishAuditsCount }}
                </div>
            </div>
            <div
                class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/50 dark:bg-rose-950/40"
            >
                <div class="text-sm font-medium text-rose-900 dark:text-rose-100">待处理评论举报</div>
                <div class="mt-1 text-3xl font-semibold tabular-nums text-rose-950 dark:text-rose-50">
                    {{ $this->pendingCommentReportsCount }}
                </div>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
        >
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">快捷入口</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                与旧版 Vue「投稿审核」工作台类似：在此汇总待办，再进入各资源列表处理。
            </p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($this->quickLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-primary-400 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-primary-500 dark:hover:bg-gray-800/80"
                    >
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $link['label'] }}</div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $link['description'] }}</p>
                        </div>
                        @if ($link['badge'] !== null && (int) $link['badge'] > 0)
                            <span
                                class="inline-flex min-w-[2rem] shrink-0 items-center justify-center rounded-full bg-primary-600 px-2 py-0.5 text-xs font-semibold text-white"
                            >
                                {{ $link['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>

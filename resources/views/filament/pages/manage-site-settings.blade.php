<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap gap-3">
            <x-filament::button type="submit">
                保存全部设置
            </x-filament::button>
            <p class="text-sm text-gray-500 dark:text-gray-400 self-center">
                统一写入 <code class="text-xs">site_settings</code> 表，与前台读取逻辑一致。
            </p>
        </div>
    </form>
</x-filament-panels::page>

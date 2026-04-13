@props([
    'id' => 'oc-confirm-modal',
    'title' => '确认操作',
    'confirmLabel' => '确定',
    'cancelLabel' => '取消',
])
<div
    id="{{ $id }}"
    class="oc-modal-overlay oc-confirm-modal hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    data-oc-confirm-root
>
    <div class="oc-modal oc-confirm-modal__panel w-full max-w-md" role="document">
        <h2 id="{{ $id }}-title" class="text-lg font-bold oc-heading m-0 mb-3">{{ $title }}</h2>
        <div class="text-sm leading-relaxed oc-muted">
            {{ $slot }}
        </div>
        <div class="flex flex-wrap justify-end gap-2 mt-5">
            <button type="button" class="btn btn-secondary text-sm" data-oc-confirm-cancel>{{ $cancelLabel }}</button>
            <button type="button" class="btn btn-primary text-sm" data-oc-confirm-ok>{{ $confirmLabel }}</button>
        </div>
    </div>
</div>

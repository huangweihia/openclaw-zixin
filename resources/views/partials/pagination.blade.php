@if ($paginator->hasPages())
    <nav class="oc-pagination" role="navigation" aria-label="分页导航">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="oc-pagination__btn oc-pagination__btn--disabled" aria-disabled="true">上一页</span>
        @else
            <a class="oc-pagination__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">上一页</a>
        @endif

        <div class="oc-pagination__meta" aria-label="分页信息">
            第 {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }} 页
            @if (method_exists($paginator, 'total'))
                · 共 {{ $paginator->total() }} 条
            @endif
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="oc-pagination__btn" href="{{ $paginator->nextPageUrl() }}" rel="next">下一页</a>
        @else
            <span class="oc-pagination__btn oc-pagination__btn--disabled" aria-disabled="true">下一页</span>
        @endif
    </nav>
@endif


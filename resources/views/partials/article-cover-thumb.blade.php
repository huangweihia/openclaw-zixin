@php
    $ph = asset('images/article-card-placeholder.svg');
    $src = ! empty($cover) ? $cover : $ph;
@endphp
<div class="oc-card-thumb bg-slate-100 overflow-hidden">
    <img
        src="{{ $src }}"
        alt=""
        class="w-full h-full object-cover"
        loading="lazy"
        data-oc-article-ph="{{ $ph }}"
        onerror="this.onerror=null;if(this.dataset.ocArticlePh)this.src=this.dataset.ocArticlePh;"
    />
</div>

{{-- 价格卡片：倒计时（deadline_at ISO）+ 名额大字号；$plan 需含 deadline_at、spots_label --}}
@php
    $deadlineIso = null;
    if (! empty($plan['deadline_at'])) {
        try {
            $e = \Carbon\Carbon::parse($plan['deadline_at']);
            if ($e->isFuture()) {
                $deadlineIso = $e->toIso8601String();
            }
        } catch (\Throwable $ignored) {
        }
    }
@endphp
@if ($deadlineIso)
    <div class="mb-3 shrink-0" data-oc-pricing-countdown="{{ $deadlineIso }}">
        <div class="text-xs font-semibold text-orange-600 mb-1">距优惠结束</div>
        <div class="text-2xl md:text-3xl font-black tabular-nums tracking-tight" style="color: #6366f1;">
            <span class="js-oc-cd">--:--:--</span>
        </div>
    </div>
@endif
@if (! empty($plan['spots_label']))
    <p class="text-base md:text-lg font-bold text-slate-700 mb-4 m-0 shrink-0">{{ $plan['spots_label'] }}</p>
@endif

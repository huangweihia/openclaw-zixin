@if (session('success'))
    <div class="oc-flash oc-flash--success oc-flash--dismiss" role="status" data-oc-flash aria-live="polite">
        <span class="oc-flash__inner">
            <svg class="oc-flash__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="oc-flash__text">{{ session('success') }}</span>
        </span>
    </div>
@endif
@if (session('warning'))
    <div class="oc-flash oc-flash--warning oc-flash--dismiss" role="alert" data-oc-flash aria-live="assertive">
        <span class="oc-flash__inner">
            <svg class="oc-flash__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="oc-flash__text">{{ session('warning') }}</span>
        </span>
    </div>
@endif
@if (session('error'))
    <div class="oc-flash oc-flash--error oc-flash--dismiss" role="alert" data-oc-flash aria-live="assertive">
        <span class="oc-flash__inner">
            <svg class="oc-flash__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="oc-flash__text">{{ session('error') }}</span>
        </span>
    </div>
@endif

@php
    $isAdmin = Auth::user()?->hasRole('owner') || in_array(Auth::user()?->role, ['owner', 'admin', 'Store Admin', 'Owner']);
    $setting = \App\Models\CompanySetting::first();
    $showBanner = $isAdmin && is_null($setting?->tax_configured_at);
@endphp

@if($showBanner)
<div x-data="{ dismissed: sessionStorage.getItem('tax_banner_dismissed') === 'true' }"
     x-show="!dismissed"
     x-cloak
     class="bg-amber-500/10 border-b border-amber-500/30 text-amber-900 dark:text-amber-200 px-4 py-3 sm:px-6 relative z-40 transition duration-200">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-500 flex items-center justify-center flex-shrink-0 border border-amber-500/30">
                <i class="fas fa-exclamation-triangle text-sm"></i>
            </div>
            <div class="text-xs sm:text-sm">
                <span class="font-bold text-slate-900 dark:text-amber-100">Tax settings have not been configured yet.</span>
                <span class="text-slate-600 dark:text-amber-200/80 block sm:inline sm:ml-1">Set your store's tax rate so it's applied automatically to every invoice.</span>
            </div>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
            <button type="button" 
                    @click="dismissed = true; sessionStorage.setItem('tax_banner_dismissed', 'true')" 
                    class="text-xs text-slate-500 dark:text-amber-400/80 hover:text-slate-700 dark:hover:text-amber-100 px-2.5 py-1.5 rounded transition font-medium">
                Dismiss
            </button>
            <a href="{{ route('settings.tax') }}" 
               class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg shadow-sm transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fas fa-percent text-[11px]"></i> Configure Tax Now
            </a>
        </div>
    </div>
</div>
@endif

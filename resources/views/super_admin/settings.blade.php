@extends('super_admin.layout')

@section('title', 'Global Settings')
@section('header', 'Settings')
@section('subheader', 'Manage global application configuration')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Application Settings --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i class="fas fa-sliders-h text-indigo-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">Application Settings</h3>
                <p class="text-xs text-slate-400">Core platform configuration</p>
            </div>
        </div>

        <form method="POST" action="{{ route('super.settings.update') }}" class="p-8 space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Application Name</label>
                    <input type="text" name="app_name" value="OwnStore"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Support Email</label>
                    <input type="email" name="support_email" value="support@ownstore.com"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Database Naming Convention</label>
                <div class="flex items-center rounded-xl overflow-hidden border border-slate-200">
                    <span class="bg-slate-50 border-r border-slate-200 px-4 py-2.5 text-slate-500 text-sm font-mono flex-shrink-0">store_</span>
                    <input type="text" value="{random8}_{tenant_id}" disabled
                        class="w-full px-4 py-2.5 text-sm text-slate-400 bg-white focus:outline-none cursor-not-allowed">
                </div>
                <p class="text-xs text-slate-400 mt-1.5">Example: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-xs text-slate-600 font-mono">store_ab12cd34_uuid</code></p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Session Lifetime (minutes)</label>
                <input type="number" name="session_lifetime" value="120" min="30" max="10080"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 max-w-xs">
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Advanced Settings --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-rose-500 text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">Advanced Configuration</h3>
                <p class="text-xs text-slate-400">Dangerous settings — change with caution</p>
            </div>
        </div>

        <div class="p-8 space-y-5">

            {{-- Auto Backup --}}
            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Auto Database Backup</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Automatically backup all tenant databases daily.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- Maintenance Mode --}}
            <div class="flex items-center justify-between p-4 rounded-xl border border-rose-100 bg-rose-50/30">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Maintenance Mode</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Put the entire platform in maintenance mode. All tenant stores will be unreachable.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
            </div>

            {{-- New Registrations --}}
            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Allow New Registrations</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Allow new stores to register and submit approval requests.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

        </div>
    </div>

    {{-- System Info --}}
    <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-800 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center">
                <i class="fas fa-info-circle text-slate-400 text-sm"></i>
            </div>
            <h3 class="text-base font-bold text-white">System Information</h3>
        </div>
        <div class="p-8 grid grid-cols-2 gap-4 font-mono text-xs">
            <div><span class="text-slate-500">PHP Version:</span> <span class="text-emerald-400">{{ phpversion() }}</span></div>
            <div><span class="text-slate-500">Laravel:</span> <span class="text-emerald-400">{{ app()->version() }}</span></div>
            <div><span class="text-slate-500">Environment:</span> <span class="text-amber-400">{{ app()->environment() }}</span></div>
            <div><span class="text-slate-500">Debug Mode:</span> <span class="{{ config('app.debug') ? 'text-rose-400' : 'text-emerald-400' }}">{{ config('app.debug') ? 'ON' : 'OFF' }}</span></div>
            <div><span class="text-slate-500">Timezone:</span> <span class="text-sky-400">{{ config('app.timezone') }}</span></div>
            <div><span class="text-slate-500">Server Time:</span> <span class="text-sky-400">{{ now()->format('Y-m-d H:i:s') }}</span></div>
        </div>
    </div>
</div>
@endsection
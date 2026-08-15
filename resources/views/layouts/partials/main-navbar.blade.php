@php
    try {
        $navUserName = \Illuminate\Support\Facades\Auth::user()?->name ?? 'there';
    } catch (\Throwable $e) {
        $navUserName = 'there';
    }

    try {
        if (function_exists('tenancy') && tenancy()->initialized) {
            $navStoreName = tenancy()->tenant?->store_name ?? \App\Models\Store::first()?->name ?? 'OwnStore';
        } else {
            $navStoreName = \App\Models\Store::first()?->name ?? 'OwnStore';
        }
    } catch (\Throwable $e) {
        $navStoreName = 'OwnStore';
    }
@endphp

<style>
    /* === VARIABLE PALETTE === */
    :root {
        --pos-bg: #0b0b0b;
        --pos-surface: #1a1a19;
        --pos-surface-2: #232323;
        --pos-border: rgba(255,255,255,0.06);
        --pos-text: #f4f4f4;
        --pos-text-muted: #8b8d99;
        --pos-blue: #378ADD;
        --pos-orange: #EF9F27;
        --pos-amber: #BA7517;
        --pos-red: #E24B4A;
        --pos-green: #1baf7a;
        --pos-purple: #8b5cf6;
        --pos-teal: #14b8a6;
        --pos-gray: #6b7280;
    }

    /* === LIGHT MODE OVERRIDES === */
    .pos-wrapper.pos-light-mode {
        --pos-bg: #f5f5f4;
        --pos-surface: #ffffff;
        --pos-surface-2: #eaeae9;
        --pos-border: rgba(0,0,0,0.08);
        --pos-text: #0b0b0b;
        --pos-text-muted: #6b7280;
    }

    /* === BASE CONTAINER === */
    .pos-wrapper {
        background-color: var(--pos-bg);
        color: var(--pos-text);
        font-family: system-ui, -apple-system, sans-serif;
        box-sizing: border-box;
        transition: background-color 0.2s;
    }

    .pos-container {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0 16px;
        width: 100%;
        box-sizing: border-box;
    }

    /* === NAV & SUBNAV === */
    .pos-nav {
        background-color: var(--pos-surface);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--pos-border);
        position: sticky;
        top: 0;
        z-index: 50;
    }
    .pos-nav-inner {
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pos-nav-left, .pos-nav-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .pos-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: var(--pos-text);
        font-weight: 900;
        font-size: 18px;
    }
    .pos-logo-icon {
        background-color: var(--pos-blue);
        color: #fff;
        padding: 6px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pos-logo-pro {
        font-size: 9px;
        color: var(--pos-blue);
        letter-spacing: 0.1em;
        font-weight: bold;
        text-transform: uppercase;
    }
    .pos-nav-tabs {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pos-tab-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        display: inline-flex;
        text-decoration: none;
    }
    @keyframes borderRotate {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .pos-gradient-btn {
        position: relative; padding: 1.5px; border-radius: 0.5rem;
        background: transparent; transition: all 0.3s ease; display: inline-flex;
    }
    .pos-gradient-btn:hover {
        background: linear-gradient(60deg, var(--pos-blue), var(--pos-purple), var(--pos-red), var(--pos-blue));
        background-size: 300% 300%; animation: borderRotate 3s ease infinite;
        box-shadow: 0 0 8px rgba(55, 138, 221, 0.4);
    }
    .pos-gradient-btn.active-tab {
        background: linear-gradient(60deg, var(--pos-blue), var(--pos-purple), var(--pos-red), var(--pos-blue));
        background-size: 300% 300%; box-shadow: 0 0 5px rgba(55, 138, 221, 0.2);
    }
    .pos-gradient-inner {
        background-color: var(--pos-surface); border-radius: 0.45rem;
        width: 100%; height: 100%; display: flex; align-items: center;
        justify-content: center; padding: 0.35rem 0.9rem; z-index: 10;
    }
    .pos-tab-btn span {
        font-size: 12px; font-weight: bold; transition: color 0.15s ease;
    }
    .pos-tab-btn.active-tab span { color: var(--pos-text); }
    .pos-tab-btn:not(.active-tab) span { color: var(--pos-text-muted); }

    .pos-nav-status {
        display: flex; align-items: center; gap: 6px; font-size: 11px;
        color: var(--pos-text-muted); background-color: var(--pos-surface);
        padding: 4px 10px; border-radius: 9999px; border: 1px solid var(--pos-border);
    }
    .pos-nav-user {
        display: flex; align-items: center; gap: 10px;
        border-left: 1px solid var(--pos-border); padding-left: 12px;
    }
    .pos-user-name { font-size: 12px; font-weight: bold; line-height: 1.2; }
    .pos-user-role { font-size: 10px; color: var(--pos-text-muted); }
    .pos-user-avatar {
        width: 32px; height: 32px; background: linear-gradient(to bottom right, var(--pos-blue), var(--pos-purple));
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: bold; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .pos-mobile-toggle {
        display: none; background: none; border: none; color: var(--pos-text-muted); cursor: pointer; padding: 6px;
    }
    .pos-mobile-nav {
        display: none; background-color: var(--pos-surface); border-bottom: 1px solid var(--pos-border);
        padding: 8px 12px;
    }
    .pos-mobile-nav-inner {
        display: flex; flex-wrap: wrap; gap: 6px;
    }

    .pos-subnav {
        background-color: var(--pos-surface-2); border-bottom: 1px solid var(--pos-border); padding: 6px 0;
    }
    .pos-subnav-inner {
        display: flex; align-items: center; gap: 12px; overflow-x: auto; scrollbar-width: none;
    }
    .pos-subnav-inner::-webkit-scrollbar { display: none; }
    .pos-subnav-link {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        min-width: 70px; padding: 6px; border-radius: 12px; text-decoration: none;
        transition: background-color 0.15s ease; cursor: pointer;
    }
    .pos-subnav-link:hover { background-color: var(--pos-surface-2); }
    .pos-subnav-icon {
        width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; transition: transform 0.15s ease;
    }
    .pos-subnav-link:hover .pos-subnav-icon { transform: scale(1.05); }
    .pos-subnav-label { font-size: 10px; font-weight: bold; color: var(--pos-text-muted); margin-top: 4px; }
    .pos-subnav-sep { width: 1px; height: 32px; background-color: var(--pos-border); flex-shrink: 0; margin: 0 4px; }

    .pos-subnav-menu {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
    }
    .pos-subnav-menu[style*="display: none"] {
        display: none !important;
    }

    @media (max-width: 640px) {
        .pos-nav-inner { padding: 0 8px; }
        .pos-nav-tabs { display: none; }
        .pos-mobile-toggle { display: block; }
        .pos-mobile-nav { display: block; }
    }
</style>

<div x-data="{
        activeTab: '{{ $defaultTab ?? 'general' }}',
        mobileMenuOpen: false,
        shortcutsOpen: false,
        darkMode: localStorage.getItem('pos_dashboard_theme') !== 'light',
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('pos_dashboard_theme', this.darkMode ? 'dark' : 'light');
            if (this.darkMode) { document.documentElement.classList.add('dark'); }
            else { document.documentElement.classList.remove('dark'); }
            
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { darkMode: this.darkMode } }));
        }
    }"
    x-init="if(darkMode) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }"
    :class="darkMode ? '' : 'pos-light-mode'"
    class="pos-wrapper mb-6">

    {{-- ============================================================ --}}
    {{-- BAND 1: SLIM NAV                                             --}}
    {{-- ============================================================ --}}
    <nav class="pos-nav">
        <div class="pos-container">
            <div class="pos-nav-inner">
                <div class="pos-nav-left">
                    <a href="{{ route('dashboard') }}" class="pos-logo">
                        <div class="pos-logo-icon">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <span>
                            {{ $navStoreName }} <span class="pos-logo-pro">PRO</span>
                        </span>
                    </a>
                    <div class="pos-nav-tabs">
                        <template x-for="tab in ['General', 'Sales', 'Purchase', 'Accounts', 'Reports']">
                            <button @click="activeTab = tab.toLowerCase()" class="pos-gradient-btn pos-tab-btn" :class="activeTab === tab.toLowerCase() ? 'active-tab' : ''">
                                <div class="pos-gradient-inner">
                                    <span x-text="tab"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="pos-nav-right">
                    <div class="pos-nav-status">
                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background-color:#22c55e;"></span>
                        <span id="nav-time" style="font-family:monospace;">--:--:--</span>
                    </div>
                    <button @click="toggleTheme()" style="background:none; border:none; color:var(--pos-text-muted); cursor:pointer; padding:6px; font-size:15px; display:flex; align-items:center; justify-content:center;">
                        <span x-show="!darkMode"><i class="fas fa-moon"></i></span>
                        <span x-show="darkMode"><i class="fas fa-sun text-yellow-400"></i></span>
                    </button>
                    <div class="pos-nav-user">
                        <div style="text-align:right;" class="hidden sm:block">
                            <div class="pos-user-name">{{ $navUserName }}</div>
                            <div class="pos-user-role">Owner</div>
                        </div>
                        <div class="pos-user-avatar">
                            {{ strtoupper(substr($navUserName, 0, 2)) }}
                        </div>
                    </div>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="pos-mobile-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Collapsible mobile menu --}}
        <div x-show="mobileMenuOpen" class="pos-mobile-nav" style="display: none;">
            <div class="pos-mobile-nav-inner">
                <template x-for="tab in ['General', 'Sales', 'Purchase', 'Accounts', 'Reports']">
                    <button @click="activeTab = tab.toLowerCase(); mobileMenuOpen = false"
                            class="pos-gradient-btn pos-tab-btn"
                            :class="activeTab === tab.toLowerCase() ? 'active-tab' : ''"
                            style="padding: 1px; margin: 2px;">
                        <div class="pos-gradient-inner" style="padding: 6px 12px;">
                            <span x-text="tab" style="font-size:11px;"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </nav>

    {{-- ============================================================ --}}
    {{-- BAND 2: SUB-NAVIGATION                                       --}}
    {{-- ============================================================ --}}
    <div class="pos-subnav">
        <div class="pos-container">
            <div class="pos-subnav-inner">
                {{-- General Submenu --}}
                <div x-show="activeTab === 'general'" class="pos-subnav-menu">
                    <a href="{{ route('settings.users') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);">
                            <i class="fas fa-users-cog text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">Access</span>
                    </a>
                    <a href="{{ route('accounts.import.show') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);">
                            <i class="fas fa-file-import text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">account import</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);">
                            <i class="fas fa-address-book text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">customer</span>
                    </a>
                    <div class="pos-subnav-sep"></div>
                    <a href="{{ route('todo') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);">
                            <i class="fas fa-clipboard-list text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">To Do</span>
                    </a>
                    <a href="{{ route('reminders.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(226,75,74,0.12); color: var(--pos-red);">
                            <i class="fas fa-bell text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">Reminder</span>
                    </a>
                    <a href="{{ route('staff.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(20,184,166,0.12); color: var(--pos-teal);">
                            <i class="fas fa-user-tie text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">Staff</span>
                    </a>
                    <a href="{{ route('reports.profit-loss') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(16,185,129,0.12); color: var(--pos-green);">
                            <i class="fas fa-chart-line text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">P&L Report</span>
                    </a>
                    <a href="{{ route('reports.daily-closing') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);">
                            <i class="fas fa-cash-register text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">Daily Close</span>
                    </a>
                    <a href="{{ route('reports.audit-log') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">Audit Log</span>
                    </a>
                    <a href="{{ route('settings.backup.download') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(107,114,128,0.12); color: var(--pos-gray);">
                            <i class="fas fa-database text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">Backup</span>
                    </a>
                </div>

                {{-- Sales Submenu --}}
                <div x-show="activeTab === 'sales'" class="pos-subnav-menu" style="display: none;">
                    <a href="{{ route('sales.pos') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-calculator text-sm"></i></div>
                        <span class="pos-subnav-label">Counter</span>
                    </a>
                    <a href="{{ route('sales.history') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-history text-sm"></i></div>
                        <span class="pos-subnav-label">History</span>
                    </a>
                    <div class="pos-subnav-sep"></div>
                    <a href="{{ route('cash-sales.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-money-bill-wave text-sm"></i></div>
                        <span class="pos-subnav-label">Cash Sales</span>
                    </a>
                    <a href="{{ route('debit-sales.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-credit-card text-sm"></i></div>
                        <span class="pos-subnav-label">CRDT Sales</span>
                    </a>
                    <a href="{{ route('refunds.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(226,75,74,0.12); color: var(--pos-red);"><i class="fas fa-undo text-sm"></i></div>
                        <span class="pos-subnav-label">Refunds</span>
                    </a>
                    <div class="pos-subnav-sep"></div>
                    <a href="{{ route('receipts.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-receipt text-sm"></i></div>
                        <span class="pos-subnav-label">Receipts</span>
                    </a>
                    <a href="{{ route('payments.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-hand-holding-usd text-sm"></i></div>
                        <span class="pos-subnav-label">Payments</span>
                    </a>
                    <a href="{{ route('transfers.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-exchange-alt text-sm"></i></div>
                        <span class="pos-subnav-label">Transfers</span>
                    </a>
                    <div class="pos-subnav-sep"></div>
                    <a href="{{ route('items.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(20,184,166,0.12); color: var(--pos-teal);"><i class="fas fa-boxes text-sm"></i></div>
                        <span class="pos-subnav-label">Items</span>
                    </a>
                    <a href="{{ route('barcodes.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(107,114,128,0.12); color: var(--pos-gray);"><i class="fas fa-barcode text-sm"></i></div>
                        <span class="pos-subnav-label">Barcodes</span>
                    </a>
                    <a href="{{ route('adjustments.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(186,117,23,0.12); color: var(--pos-amber);"><i class="fas fa-sliders-h text-sm"></i></div>
                        <span class="pos-subnav-label">Adjust</span>
                    </a>
                </div>

                {{-- Purchase Submenu --}}
                <div x-show="activeTab === 'purchase'" class="pos-subnav-menu" style="display: none;">
                    <a href="{{ route('purchases.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-file-invoice text-sm"></i></div>
                        <span class="pos-subnav-label">Cash Bill</span>
                    </a>
                    <a href="{{ route('purchases.create-credit') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-file-signature text-sm"></i></div>
                        <span class="pos-subnav-label">CRDT Bill</span>
                    </a>
                    <a href="{{ route('purchase-orders.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-shopping-cart text-sm"></i></div>
                        <span class="pos-subnav-label">Pur. Order</span>
                    </a>
                    <a href="{{ route('purchase-orders.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(79,70,229,0.12); color: var(--pos-blue);">
                            <i class="fas fa-file-invoice text-sm"></i>
                        </div>
                        <span class="pos-subnav-label">PO System</span>
                    </a>
                    <a href="{{ route('purchase-returns.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(226,75,74,0.12); color: var(--pos-red);"><i class="fas fa-reply-all text-sm"></i></div>
                        <span class="pos-subnav-label">Returns</span>
                    </a>
                    <div class="pos-subnav-sep"></div>
                    <a href="{{ route('suppliers.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-truck text-sm"></i></div>
                        <span class="pos-subnav-label">Suppliers</span>
                    </a>
                </div>

                {{-- Accounts Submenu --}}
                <div x-show="activeTab === 'accounts'" class="pos-subnav-menu" style="display: none;">
                    <a href="{{ route('journals.create') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-book text-sm"></i></div>
                        <span class="pos-subnav-label">Journal</span>
                    </a>
                    <a href="{{ route('general-ledger.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-book-open text-sm"></i></div>
                        <span class="pos-subnav-label">GLedgers</span>
                    </a>
                    <a href="{{ route('reports.accounts') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(20,184,166,0.12); color: var(--pos-teal);"><i class="fas fa-wallet text-sm"></i></div>
                        <span class="pos-subnav-label">Accounts</span>
                    </a>
                    <a href="{{ route('banks.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-university text-sm"></i></div>
                        <span class="pos-subnav-label">Banks</span>
                    </a>
                    <a href="{{ route('values.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-dollar-sign text-sm"></i></div>
                        <span class="pos-subnav-label">Values</span>
                    </a>
                </div>

                {{-- Reports Submenu --}}
                <div x-show="activeTab === 'reports'" class="pos-subnav-menu" style="display: none;">
                    <a href="{{ route('reports.index') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-chart-line text-sm"></i></div>
                        <span class="pos-subnav-label">Selected</span>
                    </a>
                    <a href="{{ route('reports.sales') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-chart-bar text-sm"></i></div>
                        <span class="pos-subnav-label">Sales Rep</span>
                    </a>
                    <a href="{{ route('reports.purchases') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-shopping-bag text-sm"></i></div>
                        <span class="pos-subnav-label">Purchase Rep</span>
                    </a>
                    <a href="{{ route('reports.profit-loss') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(16,185,129,0.12); color: var(--pos-green);"><i class="fas fa-file-invoice-dollar text-sm"></i></div>
                        <span class="pos-subnav-label">P&L Report</span>
                    </a>
                    <a href="{{ route('reports.daily-closing') }}" class="pos-subnav-link">
                        <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-cash-register text-sm"></i></div>
                        <span class="pos-subnav-label">Daily Close</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        function updateNavTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour12: false });
            const navTime = document.getElementById('nav-time');
            if (navTime) navTime.innerText = timeStr;
        }
        setInterval(updateNavTime, 1000);
        updateNavTime();
    })();
</script>

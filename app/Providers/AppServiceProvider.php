<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::body.start',
            fn(): string => \Illuminate\Support\Facades\Blade::render(<<<'HTML'
            <style>
                /* === FILAMENT OVERRIDES === */
                .fi-topbar, .fi-sidebar, .fi-header { display: none !important; }
                .fi-main { padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
                .fi-body { background: transparent !important; }

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
                    min-height: 100vh;
                    font-family: system-ui, -apple-system, sans-serif;
                    padding-bottom: 40px;
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

                /* === GREETING BAR === */
                .pos-greeting-row {
                    display: flex; align-items: center; justify-content: space-between; margin: 24px 0 20px 0;
                }
                .pos-greeting-title { font-size: 20px; font-weight: 800; margin: 0; letter-spacing: -0.02em; }
                .pos-greeting-subtitle { font-size: 12px; color: var(--pos-text-muted); margin: 2px 0 0 0; font-weight: 500; }
                .pos-greeting-badge {
                    display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: bold;
                    color: var(--pos-text-muted); background-color: var(--pos-surface); padding: 6px 12px;
                    border-radius: 9999px; border: 1px solid var(--pos-border); text-transform: uppercase;
                    letter-spacing: 0.05em;
                }

                /* === KPI CARDS === */
                .pos-grid-kpis {
                    display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;
                }
                .pos-kpi-card {
                    background-color: var(--pos-surface); border-radius: 12px; padding: 16px;
                    display: flex; flex-direction: column; justify-content: space-between; height: 145px;
                    box-sizing: border-box; text-decoration: none; color: var(--pos-text);
                    transition: transform 0.15s ease, background-color 0.15s ease;
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                    opacity: 0; transform: translateY(12px); animation: pos-fade-up 0.4s ease forwards;
                }
                .pos-kpi-card:hover { transform: translateY(-2px); background-color: var(--pos-surface-2); }
                
                .pos-kpi-card.sales { --kpi-color: var(--pos-blue); --icon-bg: rgba(55, 138, 221, 0.12); --delay-index: 1; border-left: 4px solid var(--pos-blue); animation-delay: 0.05s; }
                .pos-kpi-card.dues { --kpi-color: var(--pos-orange); --icon-bg: rgba(239, 159, 39, 0.12); --delay-index: 2; border-left: 4px solid var(--pos-orange); animation-delay: 0.1s; }
                .pos-kpi-card.stock { --kpi-color: var(--pos-amber); --icon-bg: rgba(186, 117, 23, 0.12); --delay-index: 3; border-left: 4px solid var(--pos-amber); animation-delay: 0.15s; }
                .pos-kpi-card.expiring { --kpi-color: var(--pos-red); --icon-bg: rgba(226, 75, 74, 0.12); --delay-index: 4; border-left: 4px solid var(--pos-red); animation-delay: 0.20s; }

                .pos-kpi-header { display: flex; align-items: center; justify-content: space-between; }
                .pos-kpi-title { font-size: 11px; font-weight: bold; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
                .pos-kpi-icon-wrap {
                    width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    background-color: var(--icon-bg); color: var(--kpi-color); font-size: 13px;
                }
                .pos-kpi-body { margin: 6px 0; }
                .pos-kpi-value { font-size: 22px; font-weight: 800; font-variant-numeric: tabular-nums; line-height: 1.1; }
                .pos-kpi-subtitle { font-size: 12px; color: var(--pos-text-muted); margin-top: 4px; font-weight: 500; }
                .pos-kpi-footer {
                    border-top: 1px solid var(--pos-border); padding-top: 8px; display: flex; align-items: center;
                    justify-content: space-between; font-size: 11px; color: var(--pos-text-muted);
                }
                .pos-kpi-footer-strong { color: var(--pos-text); font-weight: bold; }
                
                .pos-trend-badge { font-weight: bold; font-size: 11px; display: inline-flex; align-items: center; gap: 2px; padding: 1px 6px; border-radius: 4px; }
                .pos-trend-badge.up { color: #22c55e; background-color: rgba(34, 197, 94, 0.1); }
                .pos-trend-badge.down { color: var(--pos-red); background-color: rgba(226, 75, 74, 0.1); }
                
                .pos-dues-status { font-size: 9px; font-weight: bold; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; }
                .pos-dues-status.pending { color: var(--pos-orange); background-color: rgba(239, 159, 39, 0.12); }
                .pos-dues-status.clear { color: var(--pos-green); background-color: rgba(27, 175, 122, 0.12); }
                
                .pos-bar-container { width: 100%; margin-top: 4px; }
                .pos-bar-info { display: flex; justify-content: space-between; font-size: 9px; font-weight: bold; color: var(--pos-text-muted); margin-bottom: 2px; }
                .pos-bar-bg { width: 100%; background-color: var(--pos-surface-2); height: 5px; border-radius: 9999px; overflow: hidden; }
                .pos-bar-fill { height: 100%; border-radius: 9999px; background-color: var(--pos-orange); }
                
                .pos-status-indicator { display: flex; align-items: center; gap: 6px; font-weight: bold; }
                .pos-status-dot { width: 8px; height: 8px; border-radius: 50%; position: relative; display: inline-block; }
                .pos-status-dot.active-dot { background-color: var(--kpi-color); }
                .pos-status-dot.active-dot::after {
                    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%;
                    background-color: var(--kpi-color); animation: ping 1.2s cubic-bezier(0,0,0.2,1) infinite;
                }
                .pos-status-dot.inactive-dot { background-color: var(--pos-green); }

                /* === ALERTS GRID === */
                .pos-grid-alerts { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
                .pos-alert-box { background-color: var(--pos-surface); border: 1px solid var(--pos-border); border-radius: 12px; padding: 12px; display: flex; align-items: start; gap: 12px; box-sizing: border-box; }
                .pos-alert-icon-box { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
                .pos-alert-box.expiring .pos-alert-icon-box { background-color: rgba(226, 75, 74, 0.1); color: var(--pos-red); }
                .pos-alert-box.stock .pos-alert-icon-box { background-color: rgba(186, 117, 23, 0.1); color: var(--pos-amber); }
                .pos-alert-box.dues .pos-alert-icon-box { background-color: rgba(239, 159, 39, 0.1); color: var(--pos-orange); }
                .pos-alert-box.receivables .pos-alert-icon-box { background-color: rgba(55, 138, 221, 0.1); color: var(--pos-blue); }
                .pos-alert-content { flex-grow: 1; min-width: 0; }
                .pos-alert-title { font-size: 12px; font-weight: bold; margin: 0; color: var(--pos-text); }
                .pos-alert-desc { font-size: 10px; color: var(--pos-text-muted); margin: 2px 0 0 0; }
                .pos-alert-link { font-size: 10px; font-weight: bold; text-decoration: none; margin-left: auto; flex-shrink: 0; white-space: nowrap; }
                .pos-alert-box.expiring .pos-alert-link { color: var(--pos-red); }
                .pos-alert-box.stock .pos-alert-link { color: var(--pos-amber); }
                .pos-alert-box.dues .pos-alert-link { color: var(--pos-orange); }
                .pos-alert-box.receivables .pos-alert-link { color: var(--pos-blue); }
                .pos-alert-link:hover { text-decoration: underline; }

                /* === CHARTS GRID (2 Columns) === */
                .pos-chart-grid-2 { display: grid; grid-template-columns: 3fr 2fr; gap: 16px; margin-bottom: 20px; }
                .pos-chart-card {
                    background-color: var(--pos-surface); border-radius: 12px; padding: 16px; position: relative;
                    display: flex; flex-direction: column; justify-content: space-between; min-height: 250px; box-sizing: border-box;
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: transform 0.15s ease, background-color 0.15s ease;
                }
                .pos-chart-card:hover { transform: translateY(-2px); background-color: var(--pos-surface-2); }
                .pos-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
                .pos-card-title-group { display: flex; flex-direction: column; }
                .pos-card-title { font-size: 13px; font-weight: bold; margin: 0; color: var(--pos-text); }
                .pos-card-subtitle { font-size: 11px; color: var(--pos-text-muted); margin-top: 2px; font-weight: 500; }
                
                .pos-badge-sample {
                    background-color: rgba(255, 255, 255, 0.05); color: var(--pos-text-muted); border: 1px solid var(--pos-border);
                    font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 9999px; letter-spacing: 0.02em;
                }
                .pos-chart-legend { display: flex; align-items: center; gap: 16px; font-size: 11px; }
                .pos-legend-item { display: flex; align-items: center; gap: 6px; }
                .pos-legend-dot { width: 8px; height: 8px; border-radius: 50%; }
                .pos-legend-dot.cash { background-color: var(--pos-blue); }
                .pos-legend-dot.credit { background-color: var(--pos-orange); }
                .pos-legend-val { color: var(--pos-text); font-weight: bold; }
                .pos-chart-canvas-wrap { position: relative; flex-grow: 1; width: 100%; height: 180px; }
                
                .pos-split-legend { display: flex; justify-content: space-around; margin-top: 12px; border-top: 1px solid var(--pos-border); padding-top: 12px; }
                .pos-split-item { text-align: center; width: 50%; box-sizing: border-box; }
                .pos-split-item:first-child { border-right: 1px solid var(--pos-border); }
                .pos-split-label { display: block; font-size: 9px; font-weight: bold; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
                .pos-split-val { display: block; font-size: 14px; font-weight: 800; color: var(--pos-text); }
                .pos-split-pct { font-size: 10px; font-weight: bold; margin-top: 2px; display: inline-block; }
                .pos-split-pct.cash { color: var(--pos-blue); }
                .pos-split-pct.credit { color: var(--pos-orange); }

                /* === CHARTS GRID (3 Columns) === */
                .pos-chart-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
                .pos-pie-container { display: flex; align-items: center; gap: 16px; margin-top: 8px; }
                .pos-pie-canvas-wrap { width: 120px; height: 120px; position: relative; flex-shrink: 0; }
                .pos-pie-legend { flex-grow: 1; display: grid; grid-template-columns: 1fr; gap: 6px; }
                .pos-pie-legend-row { display: flex; align-items: center; gap: 6px; font-size: 10px; min-width: 0; }
                .pos-pie-legend-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
                .pos-pie-legend-label { color: var(--pos-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 65px; }
                .pos-pie-legend-pct { font-weight: bold; margin-left: auto; color: var(--pos-text); }

                /* === TABLES & LISTS === */
                .pos-grid-tables { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 20px; }
                .pos-table-card {
                    background-color: var(--pos-surface); border-radius: 12px; padding: 20px; box-sizing: border-box;
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: transform 0.15s ease, background-color 0.15s ease;
                }
                .pos-table-card:hover { transform: translateY(-2px); background-color: var(--pos-surface-2); }
                .pos-table-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
                .pos-table-title { font-size: 13px; font-weight: bold; margin: 0; color: var(--pos-text); }
                .pos-table-link { font-size: 11px; font-weight: bold; color: var(--pos-blue); text-decoration: none; }
                .pos-table-link:hover { text-decoration: underline; }
                .pos-rows-wrap { display: flex; flex-direction: column; }
                
                .pos-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--pos-border); box-sizing: border-box; }
                .pos-row:last-child { border-bottom: none; }
                .pos-avatar {
                    width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    font-weight: bold; font-size: 11px; flex-shrink: 0;
                }
                .pos-avatar.supplier { background-color: rgba(239, 159, 39, 0.1); color: var(--pos-orange); }
                .pos-badge-number {
                    width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
                    font-weight: bold; font-size: 11px; flex-shrink: 0; background-color: rgba(55, 138, 221, 0.1);
                    color: var(--pos-blue); border: 1px solid rgba(55, 138, 221, 0.2);
                }
                
                .pos-row-details { flex-grow: 1; min-width: 0; }
                .pos-row-name { font-size: 13px; font-weight: 600; color: var(--pos-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                .pos-row-subtitle { font-size: 11px; color: var(--pos-text-muted); margin-top: 2px; }
                .pos-row-subtitle.overdue { color: var(--pos-red); font-weight: bold; animation: pulse-dot 2s ease-in-out infinite; }
                .pos-row-subtitle.warning { color: var(--pos-orange); font-weight: 500; }
                
                .pos-row-value { font-size: 13px; font-weight: bold; text-align: right; flex-shrink: 0; }
                .pos-row-value.high { color: var(--pos-red); }
                .pos-row-value.normal { color: var(--pos-orange); }
                .pos-row-value.muted { color: var(--pos-text-muted); }
                
                .pos-table-empty { text-align: center; padding: 32px 0; color: var(--pos-text-muted); }
                .pos-table-empty-icon { font-size: 24px; margin-bottom: 6px; }
                .pos-table-empty-text { font-size: 12px; font-weight: 500; }

                /* === WAREHOUSE STOCK LEVELS === */
                .pos-wh-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
                .pos-wh-title-group { display: flex; flex-direction: column; }
                .pos-wh-title { font-size: 13px; font-weight: bold; margin: 0; }
                .pos-wh-subtitle { font-size: 11px; color: var(--pos-text-muted); margin-top: 2px; }
                .pos-wh-total-group { text-align: right; }
                .pos-wh-total-label { font-size: 11px; color: var(--pos-text-muted); }
                .pos-wh-total-val { font-size: 13px; font-weight: 900; color: var(--pos-blue); margin-top: 2px; }
                .pos-wh-row { margin-bottom: 12px; }
                .pos-wh-row:last-child { margin-bottom: 0; }
                .pos-wh-meta { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; }
                .pos-wh-name { font-weight: bold; color: var(--pos-text-muted); }
                .pos-wh-units { font-weight: bold; color: var(--pos-text-muted); }
                .pos-wh-bar-bg { width: 100%; background-color: var(--pos-surface-2); height: 8px; border-radius: 9999px; overflow: hidden; }
                .pos-wh-bar-fill { background-color: var(--pos-blue); height: 100%; border-radius: 9999px; }

                /* === QUICK ACTIONS === */
                .pos-section-title { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: var(--pos-text-muted); margin-bottom: 12px; }
                .pos-quick-actions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
                
                .pos-quick-action {
                    background-color: var(--pos-surface); border-radius: 16px; padding: 16px 12px;
                    display: flex; flex-direction: column; align-items: center; gap: 8px;
                    text-decoration: none; transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
                    text-align: center; box-sizing: border-box;
                }
                .pos-quick-action:hover {
                    transform: translateY(-2px); background-color: var(--pos-surface-2);
                    box-shadow: 0 8px 24px var(--hover-shadow-color);
                }
                
                .pos-quick-action.new-sale { --action-color: var(--pos-blue); --action-bg-tint: rgba(55, 138, 221, 0.12); --hover-shadow-color: rgba(55, 138, 221, 0.15); }
                .pos-quick-action.new-purchase { --action-color: var(--pos-green); --action-bg-tint: rgba(27, 175, 122, 0.12); --hover-shadow-color: rgba(27, 175, 122, 0.15); }
                .pos-quick-action.transfer { --action-color: var(--pos-purple); --action-bg-tint: rgba(139, 92, 246, 0.12); --hover-shadow-color: rgba(139, 92, 246, 0.15); }
                .pos-quick-action.import { --action-color: var(--pos-amber); --action-bg-tint: rgba(186, 117, 23, 0.12); --hover-shadow-color: rgba(186, 117, 23, 0.15); }
                .pos-quick-action.customer { --action-color: var(--pos-teal); --action-bg-tint: rgba(20, 184, 166, 0.12); --hover-shadow-color: rgba(20, 184, 166, 0.15); }
                .pos-quick-action.supplier { --action-color: var(--pos-orange); --action-bg-tint: rgba(239, 159, 39, 0.12); --hover-shadow-color: rgba(239, 159, 39, 0.15); }
                .pos-quick-action.reports { --action-color: var(--pos-gray); --action-bg-tint: rgba(107, 114, 128, 0.12); --hover-shadow-color: rgba(107, 114, 128, 0.15); }
                .pos-quick-action.returns { --action-color: var(--pos-red); --action-bg-tint: rgba(226, 75, 74, 0.12); --hover-shadow-color: rgba(226, 75, 74, 0.15); }
                
                .pos-action-icon-wrap {
                    width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    color: var(--action-color); background-color: var(--action-bg-tint); font-size: 14px; transition: transform 0.15s ease;
                }
                .pos-quick-action:hover .pos-action-icon-wrap { transform: scale(1.08); }
                .pos-action-label { font-size: 11px; font-weight: bold; color: var(--pos-text-muted); transition: color 0.15s ease; }
                .pos-quick-action:hover .pos-action-label { color: var(--action-color); }

                /* === FLOATING QUICK ACCESS PANEL === */
                .pos-floating-btn {
                    position: fixed;
                    bottom: 24px;
                    right: 24px;
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    background-color: var(--pos-blue);
                    color: #ffffff;
                    border: none;
                    box-shadow: 0 4px 12px rgba(55, 138, 221, 0.4);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    cursor: pointer;
                    z-index: 100;
                    transition: transform 0.2s ease, background-color 0.2s ease;
                }
                .pos-floating-btn:hover {
                    transform: scale(1.05);
                    background-color: #2b6cb0;
                }
                .pos-floating-panel {
                    position: fixed;
                    bottom: 84px;
                    right: 24px;
                    background-color: var(--pos-surface);
                    border: 1px solid var(--pos-border);
                    border-radius: 12px;
                    width: 180px;
                    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
                    z-index: 99;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    animation: pos-fade-up 0.2s ease forwards;
                }
                .pos-shortcut-item {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 10px 14px;
                    color: var(--pos-text);
                    text-decoration: none;
                    font-size: 12px;
                    font-weight: 600;
                    border-bottom: 1px solid var(--pos-border);
                    transition: background-color 0.15s ease;
                }
                .pos-shortcut-item:last-child {
                    border-bottom: none;
                }
                .pos-shortcut-item:hover {
                    background-color: var(--pos-surface-2);
                    color: var(--pos-blue);
                }
                .pos-shortcut-item i {
                    width: 16px;
                    text-align: center;
                    font-size: 13px;
                    color: var(--pos-text-muted);
                }
                .pos-shortcut-item:hover i {
                    color: var(--pos-blue);
                }

                /* === MODAL === */
                .pos-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(11, 11, 11, 0.85); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 16px; }
                .pos-modal-box { background-color: var(--pos-surface); border-radius: 16px; max-width: 500px; width: 100%; border-top: 4px solid var(--pos-blue); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); overflow: hidden; box-sizing: border-box; }
                .pos-modal-body { padding: 24px; }
                .pos-modal-flex { display: flex; gap: 16px; }
                .pos-modal-icon-wrap { width: 40px; height: 40px; border-radius: 50%; background-color: rgba(55, 138, 221, 0.1); color: var(--pos-blue); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; animation: bounce 1s infinite; }
                .pos-modal-text-group { flex-grow: 1; }
                .pos-modal-title { font-size: 16px; font-weight: bold; margin: 0; }
                .pos-modal-alert-details { background-color: var(--pos-surface-2); border: 1px solid var(--pos-border); padding: 12px; border-radius: 12px; margin-top: 16px; }
                .pos-modal-alert-title { font-size: 16px; font-weight: bold; margin: 0; color: var(--pos-text); }
                .pos-modal-alert-time { font-size: 11px; color: var(--pos-text-muted); margin: 4px 0 0 0; }
                .pos-modal-desc { font-size: 13px; color: var(--pos-text-muted); margin-top: 12px; font-weight: 500; }
                .pos-modal-footer { background-color: var(--pos-surface-2); padding: 12px 24px; border-top: 1px solid var(--pos-border); display: flex; justify-content: flex-end; }
                .pos-modal-btn { background-color: var(--pos-blue); color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; }
                .pos-modal-btn:hover { opacity: 0.95; }

                /* === KEYFRAME ANIMATIONS === */
                @keyframes pos-fade-up {
                    from { opacity: 0; transform: translateY(12px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.3} }
                @keyframes ping { 75%,100%{transform:scale(2);opacity:0} }
                @keyframes bounce { 0%,100%{transform:translateY(-25%);animation-timing-function:cubic-bezier(0.8,0,1,1)} 50%{transform:translateY(0);animation-timing-function:cubic-bezier(0,0,0.2,1)} }

                /* === RESPONSIVE BREAKPOINTS === */
                @media (max-width: 1024px) {
                    .pos-grid-kpis { grid-template-columns: repeat(2, 1fr); }
                    .pos-grid-alerts { grid-template-columns: repeat(2, 1fr); }
                    .pos-chart-grid-2 { grid-template-columns: 1fr; }
                    .pos-chart-grid-3 { grid-template-columns: 1fr; }
                    .pos-grid-tables { grid-template-columns: 1fr; }
                    .pos-quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
                }

                @media (max-width: 640px) {
                    .pos-nav-inner { padding: 0 8px; }
                    .pos-nav-tabs { display: none; }
                    .pos-mobile-toggle { display: block; }
                    .pos-mobile-nav { display: block; }
                    .pos-grid-kpis { grid-template-columns: 1fr; }
                    .pos-grid-alerts { grid-template-columns: 1fr; }
                    .pos-quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
                    .pos-greeting-row { flex-direction: column; align-items: flex-start; gap: 12px; }
                }
            </style>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js" defer></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

            {{-- ================================================================ --}}
            {{-- OUTER WRAPPER                                                    --}}
            {{-- ================================================================ --}}
            <div x-data="{
                    activeTab: 'general',
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
                class="pos-wrapper">

                {{-- ============================================================ --}}
                {{-- BAND 1: SLIM NAV                                             --}}
                {{-- ============================================================ --}}
                <nav class="pos-nav">
                    <div class="pos-container">
                        <div class="pos-nav-inner">
                            <div class="pos-nav-left">
                                <a href="#" class="pos-logo">
                                    <div class="pos-logo-icon">
                                        <i class="fas fa-shopping-basket"></i>
                                    </div>
                                    <span>
                                        OwnStore <span class="pos-logo-pro">PRO</span>
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
                                        <div class="pos-user-name">{{ $userName }}</div>
                                        <div class="pos-user-role">Owner</div>
                                    </div>
                                    <div class="pos-user-avatar">
                                        {{ strtoupper(substr($userName, 0, 2)) }}
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
                                <a href="#" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);">
                                        <i class="fas fa-palette text-sm"></i>
                                    </div>
                                    <span class="pos-subnav-label">Styles</span>
                                </a>
                                <a href="{{ route('settings.users') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);">
                                        <i class="fas fa-users-cog text-sm"></i>
                                    </div>
                                    <span class="pos-subnav-label">Access</span>
                                </a>
                                <a href="{{ route('items.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);">
                                        <i class="fas fa-plus-circle text-sm"></i>
                                    </div>
                                    <span class="pos-subnav-label">Add Items</span>
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
                                <a href="#" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(107,114,128,0.12); color: var(--pos-gray);">
                                        <i class="fas fa-database text-sm"></i>
                                    </div>
                                    <span class="pos-subnav-label">Backup</span>
                                </a>
                            </div>

                            {{-- Sales Submenu --}}
                            <div x-show="activeTab === 'sales'" class="pos-subnav-menu" style="display: none;">
                                <a href="{{ route('sales.pos') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-calculator"></i></div>
                                    <span class="pos-subnav-label">Counter</span>
                                </a>
                                <a href="{{ route('sales.history') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-history"></i></div>
                                    <span class="pos-subnav-label">History</span>
                                </a>
                                <div class="pos-subnav-sep"></div>
                                <a href="{{ route('cash-sales.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-money-bill-wave"></i></div>
                                    <span class="pos-subnav-label">Cash Sales</span>
                                </a>
                                <a href="{{ route('debit-sales.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-credit-card"></i></div>
                                    <span class="pos-subnav-label">CRDT Sales</span>
                                </a>
                                <a href="{{ route('refunds.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(226,75,74,0.12); color: var(--pos-red);"><i class="fas fa-undo"></i></div>
                                    <span class="pos-subnav-label">Refunds</span>
                                </a>
                                <div class="pos-subnav-sep"></div>
                                <a href="{{ route('receipts.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-receipt"></i></div>
                                    <span class="pos-subnav-label">Receipts</span>
                                </a>
                                <a href="{{ route('payments.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-hand-holding-usd"></i></div>
                                    <span class="pos-subnav-label">Payments</span>
                                </a>
                                <a href="{{ route('transfers.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-exchange-alt"></i></div>
                                    <span class="pos-subnav-label">Transfers</span>
                                </a>
                                <div class="pos-subnav-sep"></div>
                                <a href="/items" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(20,184,166,0.12); color: var(--pos-teal);"><i class="fas fa-boxes"></i></div>
                                    <span class="pos-subnav-label">Items</span>
                                </a>
                                <a href="{{ route('barcodes.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(107,114,128,0.12); color: var(--pos-gray);"><i class="fas fa-barcode"></i></div>
                                    <span class="pos-subnav-label">Barcodes</span>
                                </a>
                                <a href="{{ route('adjustments.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(186,117,23,0.12); color: var(--pos-amber);"><i class="fas fa-sliders-h"></i></div>
                                    <span class="pos-subnav-label">Adjust</span>
                                </a>
                            </div>

                            {{-- Purchase Submenu --}}
                            <div x-show="activeTab === 'purchase'" class="pos-subnav-menu" style="display: none;">
                                <a href="{{ route('purchases.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-file-invoice"></i></div>
                                    <span class="pos-subnav-label">Cash Bill</span>
                                </a>
                                <a href="{{ route('purchases.create-credit') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-file-signature"></i></div>
                                    <span class="pos-subnav-label">CRDT Bill</span>
                                </a>
                                <a href="{{ route('purchase-orders.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-shopping-cart"></i></div>
                                    <span class="pos-subnav-label">Pur. Order</span>
                                </a>
                                <a href="{{ route('purchase-returns.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(226,75,74,0.12); color: var(--pos-red);"><i class="fas fa-reply-all"></i></div>
                                    <span class="pos-subnav-label">Returns</span>
                                </a>
                                <div class="pos-subnav-sep"></div>
                                <a href="{{ route('suppliers.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-truck"></i></div>
                                    <span class="pos-subnav-label">Suppliers</span>
                                </a>
                            </div>

                            {{-- Accounts Submenu --}}
                            <div x-show="activeTab === 'accounts'" class="pos-subnav-menu" style="display: none;">
                                <a href="{{ route('journals.create') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-book"></i></div>
                                    <span class="pos-subnav-label">Journal</span>
                                </a>
                                <a href="{{ route('general-ledger.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-book-open"></i></div>
                                    <span class="pos-subnav-label">GLedgers</span>
                                </a>
                                <a href="{{ route('reports.accounts') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(20,184,166,0.12); color: var(--pos-teal);"><i class="fas fa-wallet"></i></div>
                                    <span class="pos-subnav-label">Accounts</span>
                                </a>
                                <a href="{{ route('banks.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(239,159,39,0.12); color: var(--pos-orange);"><i class="fas fa-university"></i></div>
                                    <span class="pos-subnav-label">Banks</span>
                                </a>
                                <a href="{{ route('values.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(27,175,122,0.12); color: var(--pos-green);"><i class="fas fa-dollar-sign"></i></div>
                                    <span class="pos-subnav-label">Values</span>
                                </a>
                            </div>

                            {{-- Reports Submenu --}}
                            <div x-show="activeTab === 'reports'" class="pos-subnav-menu" style="display: none;">
                                <a href="{{ route('reports.index') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(55,138,221,0.12); color: var(--pos-blue);"><i class="fas fa-chart-line"></i></div>
                                    <span class="pos-subnav-label">Selected</span>
                                </a>
                                <a href="{{ route('reports.sales') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(139,92,246,0.12); color: var(--pos-purple);"><i class="fas fa-print"></i></div>
                                    <span class="pos-subnav-label">View</span>
                                </a>
                                <a href="{{ route('reports.layout') }}" class="pos-subnav-link">
                                    <div class="pos-subnav-icon" style="background-color: rgba(20,184,166,0.12); color: var(--pos-teal);"><i class="fas fa-columns"></i></div>
                                    <span class="pos-subnav-label">Layout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- ROW 0: Greeting bar                                              --}}
                {{-- ================================================================ --}}
                <div class="pos-container">
                    <div class="pos-greeting-row">
                        <div>
                            <h1 class="pos-greeting-title">
                                Good {{ $greeting }}, <span style="color: var(--pos-blue);">{{ $userName }}</span>!
                            </h1>
                            <p class="pos-greeting-subtitle">{{ $todayLabel }} &mdash; Live business dashboard overview</p>
                        </div>
                        <div class="pos-greeting-badge">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: var(--pos-green); display: inline-block; margin-right: 4px;"></span>
                            <span>Live System Status</span>
                        </div>
                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- ROW 1: KPI Cards Grid                                            --}}
                {{-- ================================================================ --}}
                <div class="pos-container">
                    <div class="pos-grid-kpis">
                        
                        {{-- KPI Card 1: Today's Sales --}}
                        <div class="pos-kpi-card sales">
                            <div class="pos-kpi-header">
                                <span class="pos-kpi-title">Today's Sales</span>
                                <div class="pos-kpi-icon-wrap">
                                    <i class="fas fa-cash-register"></i>
                                </div>
                            </div>
                            <div class="pos-kpi-body">
                                <div class="pos-kpi-value">Rs. {{ number_format($todaySales, 0) }}</div>
                                <div class="pos-kpi-subtitle">{{ $todayOrderCount }} order{{ $todayOrderCount != 1 ? 's' : '' }} today</div>
                            </div>
                            <div class="pos-kpi-footer">
                                <span>Month: <span class="pos-kpi-footer-strong">Rs. {{ number_format($monthSales, 0) }}</span></span>
                                @php
                                    $salesDiff = $todaySales - $yesterdaySales;
                                    $salesTrendPct = $yesterdaySales > 0 ? ($salesDiff / $yesterdaySales) * 100 : 0;
                                @endphp
                                @if($yesterdaySales > 0)
                                    @if($salesDiff >= 0)
                                        <span class="pos-trend-badge up"><i class="fas fa-caret-up"></i> {{ number_format($salesTrendPct, 0) }}%</span>
                                    @else
                                        <span class="pos-trend-badge down"><i class="fas fa-caret-down"></i> {{ number_format(abs($salesTrendPct), 0) }}%</span>
                                    @endif
                                @else
                                    <span>—</span>
                                @endif
                            </div>
                        </div>

                        {{-- KPI Card 2: Outstanding Dues --}}
                        <div class="pos-kpi-card dues">
                            <div class="pos-kpi-header">
                                <span class="pos-kpi-title">Outstanding Dues</span>
                                <div class="pos-kpi-icon-wrap">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                            <div class="pos-kpi-body">
                                <div class="pos-kpi-value">Rs. {{ number_format($totalReceivables, 0) }}</div>
                                <div class="pos-kpi-subtitle">{{ $overdueCustomerCount }} customers pending</div>
                            </div>
                            <div class="pos-kpi-footer" style="flex-direction: column; align-items: stretch; border-top: none; padding-top: 0;">
                                @php
                                    $receivablesPct = $monthSales > 0 ? min(($totalReceivables / $monthSales) * 100, 100) : 0;
                                @endphp
                                <div class="pos-bar-container">
                                    <div class="pos-bar-info">
                                        <span>Ratio vs Monthly Sales</span>
                                        <span>{{ number_format($receivablesPct, 0) }}%</span>
                                    </div>
                                    <div class="pos-bar-bg">
                                        <div class="pos-bar-fill" style="width: {{ $receivablesPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KPI Card 3: Low Stock --}}
                        <a href="{{ route('items.index') }}" class="pos-kpi-card stock">
                            <div class="pos-kpi-header">
                                <span class="pos-kpi-title">Low Stock Alert</span>
                                <div class="pos-kpi-icon-wrap">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                            <div class="pos-kpi-body">
                                <div class="pos-kpi-value">{{ $lowStockCount }} items</div>
                                <div class="pos-kpi-subtitle">Below minimum threshold</div>
                            </div>
                            <div class="pos-kpi-footer">
                                <div class="pos-status-indicator">
                                    @if($lowStockCount > 0)
                                        <span class="pos-status-dot active-dot"></span>
                                        <span style="color: var(--pos-amber);">Needs Attention</span>
                                    @else
                                        <span class="pos-status-dot inactive-dot"></span>
                                        <span style="color: var(--pos-green);">All Stocked</span>
                                    @endif
                                </div>
                                <span>Manage &rarr;</span>
                            </div>
                        </a>

                        {{-- KPI Card 4: Expiring Soon --}}
                        <a href="{{ route('items.index') }}" class="pos-kpi-card expiring">
                            <div class="pos-kpi-header">
                                <span class="pos-kpi-title">Expiring Soon</span>
                                <div class="pos-kpi-icon-wrap">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="pos-kpi-body">
                                <div class="pos-kpi-value">{{ $expiringCount }} batches</div>
                                <div class="pos-kpi-subtitle">Expiring in 7 days</div>
                            </div>
                            <div class="pos-kpi-footer">
                                <div class="pos-status-indicator">
                                    @if($expiringCount > 0)
                                        <span class="pos-status-dot active-dot"></span>
                                        <span style="color: var(--pos-red);">Action Required</span>
                                    @else
                                        <span class="pos-status-dot inactive-dot"></span>
                                        <span style="color: var(--pos-green);">All Good</span>
                                    @endif
                                </div>
                                <span>Track &rarr;</span>
                            </div>
                        </a>

                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- LIVE ALERTS (Conditional)                                        --}}
                {{-- ================================================================ --}}
                @php
                    $alertCount = ($expiringCount > 0 ? 1 : 0) + ($lowStockCount > 0 ? 1 : 0) + ($supplierDueCount > 0 ? 1 : 0) + ($overdueCustomerCount > 0 ? 1 : 0);
                @endphp
                @if($alertCount > 0)
                <div class="pos-container">
                    <div class="pos-grid-alerts">
                        {{-- Expiring --}}
                        @if($expiringCount > 0)
                        <div class="pos-alert-box expiring">
                            <div class="pos-alert-icon-box">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="pos-alert-content">
                                <p class="pos-alert-title">Expiring Batches</p>
                                <p class="pos-alert-desc">{{ $expiringCount }} batches about to expire</p>
                            </div>
                            <a href="{{ route('items.index') }}" class="pos-alert-link">View &rarr;</a>
                        </div>
                        @endif

                        {{-- Low stock --}}
                        @if($lowStockCount > 0)
                        <div class="pos-alert-box stock">
                            <div class="pos-alert-icon-box">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="pos-alert-content">
                                <p class="pos-alert-title">Low Stock Warning</p>
                                <p class="pos-alert-desc">{{ $lowStockCount }} items below minimum</p>
                            </div>
                            <a href="{{ route('items.index') }}" class="pos-alert-link">View &rarr;</a>
                        </div>
                        @endif

                        {{-- Supplier dues --}}
                        @if($supplierDueCount > 0)
                        <div class="pos-alert-box dues">
                            <div class="pos-alert-icon-box">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="pos-alert-content">
                                <p class="pos-alert-title">Supplier Dues Pending</p>
                                <p class="pos-alert-desc">Rs. {{ number_format($totalSupplierDues, 0) }} outstanding</p>
                            </div>
                            <a href="{{ route('suppliers.index') }}" class="pos-alert-link">View &rarr;</a>
                        </div>
                        @endif

                        {{-- Customer receivables --}}
                        @if($overdueCustomerCount > 0)
                        <div class="pos-alert-box receivables">
                            <div class="pos-alert-icon-box">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="pos-alert-content">
                                <p class="pos-alert-title">Customer Receivables</p>
                                <p class="pos-alert-desc">Rs. {{ number_format($totalReceivables, 0) }} outstanding</p>
                            </div>
                            <a href="{{ route('customers.index') }}" class="pos-alert-link">View &rarr;</a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ================================================================ --}}
                {{-- ROW 2: Charts (Sales Trend + Payment Split)                      --}}
                {{-- ================================================================ --}}
                <div class="pos-container">
                    <div class="pos-chart-grid-2">
                        
                        {{-- Chart 1 Card --}}
                        <div class="pos-chart-card">
                            <div id="salesTrendBadge" class="pos-badge-sample" style="display: none; position: absolute; top: 16px; right: 16px;">Sample data</div>
                            <div class="pos-card-header">
                                <div class="pos-card-title-group">
                                    <h3 class="pos-card-title">Sales Trend — Last 30 Days</h3>
                                    <p class="pos-card-subtitle">Daily cash vs credit sales</p>
                                </div>
                                <div class="pos-chart-legend">
                                    <div class="pos-legend-item">
                                        <span class="pos-legend-dot cash"></span>
                                        <span>Cash: <strong id="trendTodayCash" class="pos-legend-val">Rs. 0</strong></span>
                                    </div>
                                    <div class="pos-legend-item">
                                        <span class="pos-legend-dot credit"></span>
                                        <span>Credit: <strong id="trendTodayCredit" class="pos-legend-val">Rs. 0</strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="pos-chart-canvas-wrap">
                                <canvas id="salesTrendChart" role="img" aria-label="Sales Trend Chart"></canvas>
                            </div>
                        </div>

                        {{-- Chart 2 Card --}}
                        <div class="pos-chart-card">
                            <div id="paymentSplitBadge" class="pos-badge-sample" style="display: none; position: absolute; top: 16px; right: 16px;">Sample data</div>
                            <div class="pos-card-header">
                                <div class="pos-card-title-group">
                                    <h3 class="pos-card-title">Payment Split Today</h3>
                                    <p class="pos-card-subtitle">Counter distribution ratio</p>
                                </div>
                            </div>
                            <div class="pos-chart-canvas-wrap" style="height: 150px;">
                                <canvas id="paymentSplitChart" role="img" aria-label="Payment Split Chart"></canvas>
                            </div>
                            <div class="pos-split-legend" id="paymentSplitLegend">
                                <div class="pos-split-item">
                                    <span class="pos-split-label">Cash Sales</span>
                                    <span class="pos-split-val" id="ps-cash-amt">Rs. 0</span>
                                    <span class="pos-split-pct cash" id="ps-cash-pct">0%</span>
                                </div>
                                <div class="pos-split-item">
                                    <span class="pos-split-label">Credit Sales</span>
                                    <span class="pos-split-val" id="ps-credit-amt">Rs. 0</span>
                                    <span class="pos-split-pct credit" id="ps-credit-pct">0%</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- ROW 3: Charts (Top Items + Category + Customer Dues)              --}}
                {{-- ================================================================ --}}
                <div class="pos-container">
                    <div class="pos-chart-grid-3">
                        
                        {{-- Chart 3 Card --}}
                        <div class="pos-chart-card">
                            <div id="topItemsBadge" class="pos-badge-sample" style="display: none; position: absolute; top: 16px; right: 16px;">Sample data</div>
                            <div>
                                <h3 class="pos-card-title">Top 5 Items This Week</h3>
                                <p class="pos-card-subtitle">Units sold in last 7 days</p>
                            </div>
                            <div class="pos-chart-canvas-wrap" style="height: 160px; margin-top: 10px;">
                                <canvas id="topItemsChart" role="img" aria-label="Top Items Chart"></canvas>
                            </div>
                        </div>

                        {{-- Chart 4 Card --}}
                        <div class="pos-chart-card">
                            <div id="categoryBadge" class="pos-badge-sample" style="display: none; position: absolute; top: 16px; right: 16px;">Sample data</div>
                            <div>
                                <h3 class="pos-card-title">Sales by Category</h3>
                                <p class="pos-card-subtitle">Revenue distribution</p>
                            </div>
                            <div class="pos-pie-container">
                                <div class="pos-pie-canvas-wrap">
                                    <canvas id="categoryChart" role="img" aria-label="Category Chart"></canvas>
                                </div>
                                <div id="categoryLegend" class="pos-pie-legend"></div>
                            </div>
                        </div>

                        {{-- Chart 5 Card --}}
                        <div class="pos-chart-card">
                            <div id="customerDuesBadge" class="pos-badge-sample" style="display: none; position: absolute; top: 16px; right: 16px;">Sample data</div>
                            <div>
                                <h3 class="pos-card-title">Top Customer Dues</h3>
                                <p class="pos-card-subtitle">Highest outstanding balances</p>
                            </div>
                            <div class="pos-chart-canvas-wrap" style="height: 160px; margin-top: 10px;">
                                <canvas id="customerDuesChart" role="img" aria-label="Customer Dues Chart"></canvas>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- ROW 3.5: Supplier Dues & Top Items Lists                         --}}
                {{-- ================================================================ --}}
                <div class="pos-container">
                    <div class="pos-grid-tables">
                        
                        <!-- Supplier Dues List -->
                        <div class="pos-table-card">
                            <div class="pos-table-header">
                                <h3 class="pos-table-title">Supplier Dues</h3>
                                <a href="{{ route('suppliers.index') }}" class="pos-table-link">View all &rarr;</a>
                            </div>
                            <div class="pos-rows-wrap">
                                @forelse($supplierDues as $sup)
                                <div class="pos-row">
                                    <div class="pos-avatar supplier">
                                        {{ strtoupper(substr($sup->name, 0, 2)) }}
                                    </div>
                                    <div class="pos-row-details">
                                        <div class="pos-row-name">{{ $sup->name }}</div>
                                        @if(isset($sup->credit_days) && $sup->credit_days && isset($sup->last_purchase_date) && $sup->last_purchase_date)
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($sup->last_purchase_date)->addDays($sup->credit_days);
                                                $daysLeft = now()->diffInDays($dueDate, false);
                                            @endphp
                                            <div class="pos-row-subtitle {{ $daysLeft < 0 ? 'overdue' : ($daysLeft < 7 ? 'warning' : '') }}">
                                                {{ $daysLeft < 0 ? 'Overdue by ' . abs((int)$daysLeft) . ' days' : 'Due in ' . (int)$daysLeft . ' days' }}
                                            </div>
                                        @else
                                            <div class="pos-row-subtitle">No due date set</div>
                                        @endif
                                    </div>
                                    <div class="pos-row-value {{ $sup->current_balance > 50000 ? 'high' : 'normal' }}">
                                        Rs. {{ number_format($sup->current_balance, 0) }}
                                    </div>
                                </div>
                                @empty
                                <div class="pos-table-empty">
                                    <div class="pos-table-empty-icon"><i class="fas fa-check-circle" style="color: var(--pos-green);"></i></div>
                                    <div class="pos-table-empty-text">No outstanding supplier dues</div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Top Weekly Items List -->
                        <div class="pos-table-card">
                            <div class="pos-table-header">
                                <h3 class="pos-table-title">Top Items This Week</h3>
                                <a href="{{ route('items.index') }}" class="pos-table-link">View all &rarr;</a>
                            </div>
                            <div class="pos-rows-wrap">
                                @forelse($topItems as $item)
                                <div class="pos-row">
                                    <div class="pos-badge-number">
                                        #{{ $loop->iteration }}
                                    </div>
                                    <div class="pos-row-details">
                                        <div class="pos-row-name">{{ $item->item_name }}</div>
                                        <div class="pos-row-subtitle">{{ number_format($item->qty_sold, 0) }} units sold</div>
                                    </div>
                                    <div class="pos-row-value muted">
                                        Rs. {{ number_format($item->revenue, 0) }}
                                    </div>
                                </div>
                                @empty
                                <div class="pos-table-empty">
                                    <div class="pos-table-empty-icon"><i class="fas fa-box-open"></i></div>
                                    <div class="pos-table-empty-text">No sales data this week yet</div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================================================================ --}}
                {{-- Warehouse Stock Levels Section (Conditional)                      --}}
                {{-- ================================================================ --}}
                @if(count($godams) > 0)
                <div class="pos-container" style="margin-bottom: 20px;">
                    <div class="pos-table-card">
                        <div class="pos-wh-info">
                            <div class="pos-wh-title-group">
                                <h3 class="pos-wh-title">Warehouse Stock Levels</h3>
                                <p class="pos-wh-subtitle">Inventory distribution across locations</p>
                            </div>
                            <div class="pos-wh-total-group">
                                <span class="pos-wh-total-label">Total Stock Value</span>
                                <div class="pos-wh-total-val">Rs. {{ number_format($totalStockValue, 0) }}</div>
                            </div>
                        </div>
                        @php
                            $stockRows = [];
                            $stockRows[] = ['label' => 'Counter Floor', 'qty' => $shopFloorStock, 'icon' => 'fa-store text-blue-500'];
                            foreach ($godams as $godam) {
                                $qty = isset($godamStock[$godam->id]) ? $godamStock[$godam->id]->total_qty : 0;
                                $stockRows[] = ['label' => $godam->name, 'qty' => $qty, 'icon' => 'fa-warehouse text-purple-500'];
                            }
                            $maxQty = max(collect($stockRows)->max('qty'), 1);
                        @endphp
                        <div>
                            @foreach($stockRows as $row)
                            @php
                                $barPct = round(($row['qty'] / $maxQty) * 100);
                                if ($row['qty'] == 0) { $barPct = 0; }
                            @endphp
                            <div class="pos-wh-row">
                                <div class="pos-wh-meta">
                                    <span class="pos-wh-name"><i class="fas {{ $row['icon'] }}" style="margin-right: 6px;"></i>{{ $row['label'] }}</span>
                                    <span class="pos-wh-units">{{ number_format($row['qty'], 0) }} units</span>
                                </div>
                                <div class="pos-wh-bar-bg">
                                    <div class="pos-wh-bar-fill" style="width: {{ $barPct }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- ================================================================ --}}
                {{-- ROW 4: Quick Actions Grid                                         --}}
                {{-- ================================================================ --}}
                <div class="pos-container">
                    <h3 class="pos-section-title">Quick Actions</h3>
                    <div class="pos-quick-actions-grid">
                        
                        <a href="{{ route('sales.pos') }}" class="pos-quick-action new-sale">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <span class="pos-action-label">New Sale</span>
                        </a>

                        <a href="{{ route('purchases.create') }}" class="pos-quick-action new-purchase">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <span class="pos-action-label">New Purchase</span>
                        </a>

                        <a href="{{ route('stock-transfers.create') }}" class="pos-quick-action transfer">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <span class="pos-action-label">Transfer Stock</span>
                        </a>

                        <a href="{{ route('items.import-preview') }}" class="pos-quick-action import">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-file-import"></i>
                            </div>
                            <span class="pos-action-label">Import Items</span>
                        </a>

                        <a href="{{ route('customers.create') }}" class="pos-quick-action customer">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span class="pos-action-label">Add Customer</span>
                        </a>

                        <a href="{{ route('suppliers.create') }}" class="pos-quick-action supplier">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-truck"></i>
                            </div>
                            <span class="pos-action-label">Add Supplier</span>
                        </a>

                        <a href="{{ route('reports.sales') }}" class="pos-quick-action reports">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <span class="pos-action-label">Reports</span>
                        </a>

                        <a href="{{ route('supplier-returns.create') }}" class="pos-quick-action returns">
                            <div class="pos-action-icon-wrap">
                                <i class="fas fa-reply"></i>
                            </div>
                            <span class="pos-action-label">Process Return</span>
                        </a>

                    </div>
                </div>

                {{-- Quick Access Shortcuts Panel --}}
                <button @click.stop="shortcutsOpen = !shortcutsOpen" class="pos-floating-btn">
                    <i class="fas fa-bolt"></i>
                </button>

                <div x-show="shortcutsOpen" @click.away="shortcutsOpen = false" class="pos-floating-panel" style="display: none;">
                    <a href="/admin" class="pos-shortcut-item">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('items.index') }}" class="pos-shortcut-item">
                        <i class="fas fa-boxes"></i>
                        <span>Items List</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="pos-shortcut-item">
                        <i class="fas fa-truck"></i>
                        <span>Suppliers</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="pos-shortcut-item">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="pos-shortcut-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('settings.general') }}" class="pos-shortcut-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>

                {{-- Reminder Modal --}}
                <audio id="alertSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

                <div id="reminderModal" class="pos-modal-overlay" style="display: none;">
                    <div class="pos-modal-box">
                        <div class="pos-modal-body">
                            <div class="pos-modal-flex">
                                <div class="pos-modal-icon-wrap">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="pos-modal-text-group">
                                    <h3 class="pos-modal-title">Reminder Alert!</h3>
                                    
                                    <div class="pos-modal-alert-details">
                                        <p id="reminderTitle" class="pos-modal-alert-title">Title goes here...</p>
                                        <p class="pos-modal-alert-time"><i class="far fa-clock" style="margin-right: 4px;"></i> Just Now</p>
                                    </div>

                                    <p class="pos-modal-desc">
                                        This task is due now. Please take immediate action.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="pos-modal-footer">
                            <button type="button" onclick="closeReminderModal()" class="pos-modal-btn">
                                Acknowledge &amp; Close
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    function checkReminders() {
                        fetch('/reminders/check')
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'found') {
                                    const sound = document.getElementById('alertSound');
                                    if (sound) sound.play().catch(error => console.log('Autoplay prevented by browser'));
                                    const reminder = data.reminders[0];
                                    const titleEl = document.getElementById('reminderTitle');
                                    const modalEl = document.getElementById('reminderModal');
                                    if (titleEl) titleEl.innerText = reminder.title;
                                    if (modalEl) modalEl.style.display = 'flex';
                                }
                            })
                            .catch(error => console.error('Error checking reminders:', error));
                    }

                    function closeReminderModal() {
                        const modal = document.getElementById('reminderModal');
                        const sound = document.getElementById('alertSound');
                        if (modal) modal.style.display = 'none';
                        if (sound) { sound.pause(); sound.currentTime = 0; }
                    }

                    // -----------------------------------------------
                    // Helper: destroy a Chart.js instance on a canvas
                    // if it already exists (prevents re-init errors)
                    // -----------------------------------------------
                    function destroyChart(id) {
                        if (typeof Chart === 'undefined') return;
                        const canvas = document.getElementById(id);
                        if (!canvas) return;
                        const existing = Chart.getChart(canvas);
                        if (existing) existing.destroy();
                    }

                    // -----------------------------------------------
                    // Main chart + nav initializer — called both on
                    // initial DOMContentLoaded and on every Livewire
                    // SPA navigation so charts re-render correctly.
                    // -----------------------------------------------
                    function initPosCharts() {
                        if (typeof Chart === 'undefined') {
                            // Chart.js not yet loaded (defer race) — retry shortly
                            setTimeout(initPosCharts, 200);
                            return;
                        }

                        function updateNavTime() {
                            const now = new Date();
                            const timeStr = now.toLocaleTimeString('en-US', { hour12: false });
                            const navTime = document.getElementById('nav-time');
                            if (navTime) navTime.innerText = timeStr;
                        }
                        setInterval(updateNavTime, 1000);
                        updateNavTime();

                        // ----------------------------------------------------
                        // Theme integration helper variables for Chart.js
                        // ----------------------------------------------------
                        const isDark = localStorage.getItem('pos_dashboard_theme') !== 'light';
                        const getThemeColors = (dark) => ({
                            grid: dark ? 'rgba(255, 255, 255, 0.03)' : 'rgba(0, 0, 0, 0.03)',
                            text: dark ? '#8b8d99' : '#6b7280',
                            ttBg: dark ? '#1a1a19' : '#ffffff',
                            ttText: dark ? '#e5e7eb' : '#0b0b0b',
                            ttBorder: dark ? '#232323' : '#eaeae9',
                            border: dark ? '#1a1a19' : '#ffffff'
                        });

                        let currentColors = getThemeColors(isDark);
                        const charts = [];

                        // ----------------------------------------------------
                        // Chart 1: Sales Trend
                        // ----------------------------------------------------
                        try {
                            var trendData = {!! $salesTrend !!};
                            var hasTrend = trendData.labels && trendData.labels.length > 0 && 
                                (trendData.cash.some(v => v > 0) || trendData.credit.some(v => v > 0));
                            
                            if (!hasTrend) {
                                var demoLabels = [];
                                var demoCash = [];
                                var demoCredit = [];
                                for (var i = 29; i >= 0; i--) {
                                    var d = new Date();
                                    d.setDate(d.getDate() - i);
                                    demoLabels.push(d.getDate() + ' ' + ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()]);
                                    demoCash.push(Math.floor(Math.random() * 60000) + 20000);
                                    demoCredit.push(Math.floor(Math.random() * 20000) + 5000);
                                }
                                trendData = {
                                    labels: demoLabels,
                                    cash: demoCash,
                                    credit: demoCredit
                                };
                                document.getElementById('salesTrendBadge').style.display = 'inline-block';
                            }

                            var todayCashVal = trendData.cash[trendData.cash.length - 1] || 0;
                            var todayCreditVal = trendData.credit[trendData.credit.length - 1] || 0;
                            document.getElementById('trendTodayCash').innerText = 'Rs. ' + todayCashVal.toLocaleString();
                            document.getElementById('trendTodayCredit').innerText = 'Rs. ' + todayCreditVal.toLocaleString();

                            destroyChart('salesTrendChart');
                            const salesTrendEl = document.getElementById('salesTrendChart');
                            if (!salesTrendEl) { throw new Error('salesTrendChart canvas missing'); }
                            var chart1 = new Chart(salesTrendEl, {
                                type: 'line',
                                data: {
                                    labels: trendData.labels,
                                    datasets: [
                                        {
                                            label: 'Cash',
                                            data: trendData.cash,
                                            borderColor: '#378ADD',
                                            borderWidth: 2,
                                            tension: 0.35,
                                            pointRadius: 1,
                                            pointHoverRadius: 5,
                                            fill: true,
                                            backgroundColor: 'rgba(55, 138, 221, 0.05)'
                                        },
                                        {
                                            label: 'Credit',
                                            data: trendData.credit,
                                            borderColor: '#EF9F27',
                                            borderWidth: 2,
                                            tension: 0.35,
                                            pointRadius: 1,
                                            pointHoverRadius: 5,
                                            fill: true,
                                            backgroundColor: 'rgba(239, 159, 39, 0.05)'
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            backgroundColor: currentColors.ttBg,
                                            titleColor: isDark ? '#ffffff' : '#0b0b0b',
                                            bodyColor: currentColors.ttText,
                                            borderColor: currentColors.ttBorder,
                                            borderWidth: 1
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { color: currentColors.grid },
                                            ticks: {
                                                color: currentColors.text,
                                                font: { size: 9 },
                                                autoSkip: true,
                                                maxTicksLimit: 7
                                            }
                                        },
                                        y: {
                                            grid: { color: currentColors.grid },
                                            ticks: {
                                                color: currentColors.text,
                                                font: { size: 9 },
                                                callback: function(value) {
                                                    return 'Rs. ' + (value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                            charts.push(chart1);
                        } catch(e) { console.error('Sales Trend Chart error:', e); }

                        // ----------------------------------------------------
                        // Chart 2: Payment Split
                        // ----------------------------------------------------
                        try {
                            var splitData = {!! $paymentSplit !!};
                            var hasSplit = splitData.cashAmt > 0 || splitData.creditAmt > 0;
                            
                            if (!hasSplit) {
                                splitData = {
                                    cash: 70,
                                    credit: 30,
                                    cashAmt: 59150,
                                    creditAmt: 25350
                                };
                                document.getElementById('paymentSplitBadge').style.display = 'inline-block';
                            }

                            document.getElementById('ps-cash-amt').innerText = 'Rs. ' + Number(splitData.cashAmt).toLocaleString();
                            document.getElementById('ps-cash-pct').innerText = splitData.cash + '%';
                            document.getElementById('ps-credit-amt').innerText = 'Rs. ' + Number(splitData.creditAmt).toLocaleString();
                            document.getElementById('ps-credit-pct').innerText = splitData.credit + '%';

                            var centerTextPlugin = {
                                id: 'centerText',
                                afterDraw(chart) {
                                    const { ctx, chartArea: { top, bottom, left, right, width, height } } = chart;
                                    const isLMode = document.querySelector('.pos-wrapper').classList.contains('pos-light-mode');
                                    ctx.save();
                                    ctx.font = 'bold 12px sans-serif';
                                    ctx.fillStyle = isLMode ? '#0b0b0b' : '#f4f4f4';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    const totalVal = chart.config.options.plugins.centerText.totalAmount || 0;
                                    const totalFormatted = 'Rs. ' + totalVal.toLocaleString('en-US', { maximumFractionDigits: 0 });
                                    ctx.fillText(totalFormatted, left + width / 2, top + height / 2 - 8);
                                    
                                    ctx.font = 'normal 9px sans-serif';
                                    ctx.fillStyle = isLMode ? '#6b7280' : '#8b8d99';
                                    ctx.fillText("Total Sales", left + width / 2, top + height / 2 + 8);
                                    ctx.restore();
                                }
                            };

                            destroyChart('paymentSplitChart');
                            const paymentSplitEl = document.getElementById('paymentSplitChart');
                            if (!paymentSplitEl) { throw new Error('paymentSplitChart canvas missing'); }
                            var chart2 = new Chart(paymentSplitEl, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Cash', 'Credit'],
                                    datasets: [{
                                        data: [splitData.cashAmt, splitData.creditAmt],
                                        backgroundColor: ['#378ADD', '#EF9F27'],
                                        borderColor: currentColors.border,
                                        borderWidth: 2
                                    }]
                                },
                                plugins: [centerTextPlugin],
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '68%',
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: currentColors.ttBg,
                                            titleColor: isDark ? '#ffffff' : '#0b0b0b',
                                            bodyColor: currentColors.ttText,
                                            borderColor: currentColors.ttBorder,
                                            borderWidth: 1,
                                            callbacks: {
                                                label: function(context) {
                                                    const value = context.raw;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const pct = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
                                                    return context.label + ': Rs. ' + value.toLocaleString() + ' (' + pct + '%)';
                                                }
                                            }
                                        },
                                        centerText: {
                                            totalAmount: splitData.cashAmt + splitData.creditAmt
                                        }
                                    }
                                }
                            });
                            charts.push(chart2);
                        } catch(e) { console.error('Payment Split error:', e); }

                        // ----------------------------------------------------
                        // Chart 3: Top 5 Items
                        // ----------------------------------------------------
                        try {
                            var topItemsData = {!! $topItemsChart !!};
                            var hasTopItems = topItemsData.labels && topItemsData.labels.length > 0 && topItemsData.data.some(v => v > 0);
                            
                            if (!hasTopItems) {
                                topItemsData = {
                                    labels: ['Sugar 1kg', 'Basmati Rice', 'Cooking Oil', 'Milk 1L', 'Flour 2kg'],
                                    data: [284, 196, 143, 312, 228]
                                };
                                document.getElementById('topItemsBadge').style.display = 'inline-block';
                            }

                            destroyChart('topItemsChart');
                            const topItemsEl = document.getElementById('topItemsChart');
                            if (!topItemsEl) { throw new Error('topItemsChart canvas missing'); }
                            var chart3 = new Chart(topItemsEl, {
                                type: 'bar',
                                data: {
                                    labels: topItemsData.labels,
                                    datasets: [{
                                        data: topItemsData.data,
                                        backgroundColor: ['#378ADD', '#4f9ee3', '#6bb3ed', '#88c9f5', '#a5defc'],
                                        borderRadius: 3
                                    }]
                                },
                                options: {
                                    indexAxis: 'y',
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: currentColors.ttBg,
                                            titleColor: isDark ? '#ffffff' : '#0b0b0b',
                                            bodyColor: currentColors.ttText,
                                            borderColor: currentColors.ttBorder,
                                            borderWidth: 1
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { color: currentColors.grid },
                                            ticks: { color: currentColors.text, font: { size: 9 } }
                                        },
                                        y: {
                                            grid: { display: false },
                                            ticks: {
                                                color: currentColors.text,
                                                font: { size: 9 },
                                                callback: function(value) {
                                                    const label = this.getLabelForValue(value);
                                                    return label.length > 10 ? label.substring(0, 10) + '...' : label;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                            charts.push(chart3);
                        } catch(e) { console.error('Top Items Chart error:', e); }

                        // ----------------------------------------------------
                        // Chart 4: Sales by Category
                        // ----------------------------------------------------
                        try {
                            var categoryData = {!! $categoryChart !!};
                            var hasCategory = categoryData.labels && categoryData.labels.length > 0 && categoryData.data.some(v => v > 0);
                            
                            if (!hasCategory) {
                                categoryData = {
                                    labels: ['Grocery', 'Dairy', 'Bakery', 'Beverages', 'Other'],
                                    data: [38, 22, 15, 12, 13]
                                };
                                document.getElementById('categoryBadge').style.display = 'inline-block';
                            }

                            destroyChart('categoryChart');
                            const categoryEl = document.getElementById('categoryChart');
                            if (!categoryEl) { throw new Error('categoryChart canvas missing'); }
                            var chart4 = new Chart(categoryEl, {
                                type: 'pie',
                                data: {
                                    labels: categoryData.labels,
                                    datasets: [{
                                        data: categoryData.data,
                                        backgroundColor: ['#378ADD','#1baf7a','#EF9F27','#8b5cf6','#E24B4A','#6b7280'],
                                        borderColor: currentColors.border,
                                        borderWidth: 1.5
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: currentColors.ttBg,
                                            titleColor: isDark ? '#ffffff' : '#0b0b0b',
                                            bodyColor: currentColors.ttText,
                                            borderColor: currentColors.ttBorder,
                                            borderWidth: 1,
                                            callbacks: {
                                                label: function(context) {
                                                    const sum = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const value = context.raw;
                                                    const pct = sum > 0 ? ((value / sum) * 100).toFixed(1) : 0;
                                                    return context.label + ': Rs. ' + value.toLocaleString() + ' (' + pct + '%)';
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                            charts.push(chart4);

                            const legendDiv = document.getElementById('categoryLegend');
                            if (legendDiv) {
                                let html = '';
                                const sum = categoryData.data.reduce((a, b) => a + b, 0);
                                const colors = ['#378ADD','#1baf7a','#EF9F27','#8b5cf6','#E24B4A','#6b7280'];
                                categoryData.labels.forEach((label, idx) => {
                                    const val = categoryData.data[idx];
                                    const pct = sum > 0 ? ((val / sum) * 100).toFixed(0) : 0;
                                    const color = colors[idx] || '#6b7280';
                                    html += `
                                        <div class="pos-pie-legend-row">
                                            <span class="pos-pie-legend-dot" style="background-color: ${color}"></span>
                                            <span class="pos-pie-legend-label" title="${label}">${label}</span>
                                            <span class="pos-pie-legend-pct">${pct}%</span>
                                        </div>
                                    `;
                                });
                                legendDiv.innerHTML = html;
                            }
                        } catch(e) { console.error('Category Chart error:', e); }

                        // ----------------------------------------------------
                        // Chart 5: Customer Dues
                        // ----------------------------------------------------
                        try {
                            var customerDuesData = {!! $customerDuesChart !!};
                            var hasDues = customerDuesData.labels && customerDuesData.labels.length > 0 && customerDuesData.data.some(v => v > 0);
                            
                            if (!hasDues) {
                                customerDuesData = {
                                    labels: ['Ali Khan', 'Sara Ahmed', 'Usman Co.', 'Raza Store', 'M. Brothers'],
                                    data: [65000, 38000, 22000, 15000, 8000]
                                };
                                document.getElementById('customerDuesBadge').style.display = 'inline-block';
                            }

                            const duesChartColors = customerDuesData.data.map(v => v > 50000 ? '#E24B4A' : (v >= 20000 ? '#EF9F27' : '#BA7517'));

                            destroyChart('customerDuesChart');
                            const customerDuesEl = document.getElementById('customerDuesChart');
                            if (!customerDuesEl) { throw new Error('customerDuesChart canvas missing'); }
                            var chart5 = new Chart(customerDuesEl, {
                                type: 'bar',
                                data: {
                                    labels: customerDuesData.labels,
                                    datasets: [{
                                        data: customerDuesData.data,
                                        backgroundColor: duesChartColors,
                                        borderRadius: 3
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: currentColors.ttBg,
                                            titleColor: isDark ? '#ffffff' : '#0b0b0b',
                                            bodyColor: currentColors.ttText,
                                            borderColor: currentColors.ttBorder,
                                            borderWidth: 1,
                                            callbacks: {
                                                label: function(context) {
                                                    return 'Due: Rs. ' + context.raw.toLocaleString();
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { display: false },
                                            ticks: {
                                                color: currentColors.text,
                                                font: { size: 9 },
                                                maxRotation: 0
                                            }
                                        },
                                        y: {
                                            grid: { color: currentColors.grid },
                                            ticks: {
                                                color: currentColors.text,
                                                font: { size: 9 },
                                                callback: function(value) {
                                                    return 'Rs. ' + (value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                            charts.push(chart5);
                        } catch(e) { console.error('Customer Dues Chart error:', e); }

                        // ----------------------------------------------------
                        // Theme Change Event Listener to dynamically update charts
                        // ----------------------------------------------------
                        window.addEventListener('theme-changed', (e) => {
                            const dark = e.detail.darkMode;
                            currentColors = getThemeColors(dark);

                            charts.forEach(chart => {
                                // Update scales grid and ticks
                                if (chart.options.scales) {
                                    if (chart.options.scales.x) {
                                        if (chart.options.scales.x.grid) chart.options.scales.x.grid.color = currentColors.grid;
                                        if (chart.options.scales.x.ticks) chart.options.scales.x.ticks.color = currentColors.text;
                                    }
                                    if (chart.options.scales.y) {
                                        if (chart.options.scales.y.grid) chart.options.scales.y.grid.color = currentColors.grid;
                                        if (chart.options.scales.y.ticks) chart.options.scales.y.ticks.color = currentColors.text;
                                    }
                                }
                                // Update tooltip options
                                if (chart.options.plugins && chart.options.plugins.tooltip) {
                                    chart.options.plugins.tooltip.backgroundColor = currentColors.ttBg;
                                    chart.options.plugins.tooltip.titleColor = dark ? '#ffffff' : '#0b0b0b';
                                    chart.options.plugins.tooltip.bodyColor = currentColors.ttText;
                                    chart.options.plugins.tooltip.borderColor = currentColors.ttBorder;
                                }
                                // Update chart datasets
                                chart.data.datasets.forEach(dataset => {
                                    if (dataset.borderColor && dataset.borderColor !== '#378ADD' && dataset.borderColor !== '#EF9F27') {
                                        dataset.borderColor = currentColors.border;
                                    }
                                });
                                chart.update();
                            });
                        });
                    } // end initPosCharts()

                    // -----------------------------------------------
                    // Register initPosCharts on initial load AND on
                    // every Livewire SPA navigation (Livewire 3+)
                    // -----------------------------------------------
                    setInterval(checkReminders, 30000);

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => {
                            checkReminders();
                            initPosCharts();
                        });
                    } else {
                        // DOM already ready (e.g. script injected after parse)
                        checkReminders();
                        initPosCharts();
                    }

                    // Livewire v3 navigation hook
                    document.addEventListener('livewire:navigated', () => {
                        initPosCharts();
                    });
                </script>
            </div>
            HTML, [
                // ----------------------------------------------------------------
                // Greeting data
                // ----------------------------------------------------------------
                'greeting'    => (function() {
                    $h = (int) now()->format('H');
                    return $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
                })(),
                'userName'    => (function() {
                    try {
                        return \Illuminate\Support\Facades\Auth::user()?->name ?? 'there';
                    } catch (\Throwable $e) {
                        return 'there';
                    }
                })(),
                'todayLabel'  => now()->format('l, d F Y'),

                // ----------------------------------------------------------------
                // KPI: Today's sales
                // ----------------------------------------------------------------
                'todaySales' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today())
                            ->sum('grand_total');
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'todayOrderCount' => (function() {
                    try {
                        return (int) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today())
                            ->count();
                    } catch (\Throwable $e) { return 0; }
                })(),
                'yesterdaySales' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today()->subDay())
                            ->sum('grand_total');
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'monthSales' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereMonth('sale_date', now()->month)
                            ->whereYear('sale_date', now()->year)
                            ->sum('grand_total');
                    } catch (\Throwable $e) { return 0.0; }
                })(),

                // ----------------------------------------------------------------
                // KPI: Cash vs Debit today
                // ----------------------------------------------------------------
                'todayCash' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today())
                            ->where('payment_mode', 'Cash')
                            ->sum('grand_total');
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'todayDebit' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today())
                            ->where('payment_mode', 'Debit')
                            ->sum('grand_total');
                    } catch (\Throwable $e) { return 0.0; }
                })(),

                // ----------------------------------------------------------------
                // KPI: Customer receivables
                // ----------------------------------------------------------------
                'totalReceivables' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('customers')
                            ->where('balance', '>', 0)
                            ->sum('balance');
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'overdueCustomerCount' => (function() {
                    try {
                        return (int) \Illuminate\Support\Facades\DB::table('customers')
                            ->where('balance', '>', 0)
                            ->count();
                    } catch (\Throwable $e) { return 0; }
                })(),

                // ----------------------------------------------------------------
                // KPI: Low stock (try min_stock_level first, fall back to min_stock)
                // ----------------------------------------------------------------
                'lowStockCount' => (function() {
                    try {
                        // Try min_stock_level column (added in later migration)
                        return (int) \Illuminate\Support\Facades\DB::table('items')
                            ->whereNotNull('min_stock_level')
                            ->where('min_stock_level', '>', 0)
                            ->where('on_hand', '>', 0)
                            ->whereColumn('on_hand', '<', 'min_stock_level')
                            ->count();
                    } catch (\Throwable $e) {
                        // Fall back to min_stock column
                        try {
                            return (int) \Illuminate\Support\Facades\DB::table('items')
                                ->where('min_stock', '>', 0)
                                ->where('on_hand', '>', 0)
                                ->whereColumn('on_hand', '<', 'min_stock')
                                ->count();
                        } catch (\Throwable $e2) {
                            return 0;
                        }
                    }
                })(),

                // ----------------------------------------------------------------
                // KPI: Expiring batches within 7 days
                // ----------------------------------------------------------------
                'expiringCount' => (function() {
                    try {
                        return (int) \Illuminate\Support\Facades\DB::table('batches')
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '>=', today()->toDateString())
                            ->where('expires_at', '<=', today()->addDays(7)->toDateString())
                            ->where('quantity_available', '>', 0)
                            ->count();
                    } catch (\Throwable $e) { return 0; }
                })(),

                // ----------------------------------------------------------------
                // Weekly sales chart data (last 7 days)
                // ----------------------------------------------------------------
                'weeklySales' => (function() {
                    try {
                        $rows = \Illuminate\Support\Facades\DB::table('sales')
                            ->selectRaw('DATE(sale_date) as date, SUM(grand_total) as total')
                            ->where('sale_date', '>=', now()->subDays(6)->startOfDay())
                            ->groupByRaw('DATE(sale_date)')
                            ->orderBy('date')
                            ->get()
                            ->keyBy('date');

                        // Ensure all 7 days present (fill gaps with 0)
                        $result = [];
                        for ($i = 6; $i >= 0; $i--) {
                            $d = now()->subDays($i)->toDateString();
                            $result[] = (object)[
                                'date'  => $d,
                                'total' => isset($rows[$d]) ? (float)$rows[$d]->total : 0.0,
                            ];
                        }
                        return $result;
                    } catch (\Throwable $e) {
                        // Return 7 zero-filled days
                        $result = [];
                        for ($i = 6; $i >= 0; $i--) {
                            $result[] = (object)['date' => now()->subDays($i)->toDateString(), 'total' => 0.0];
                        }
                        return $result;
                    }
                })(),

                // ----------------------------------------------------------------
                // Top selling items (last 7 days, qty + revenue)
                // ----------------------------------------------------------------
                'topItems' => (function() {
                    try {
                        return \Illuminate\Support\Facades\DB::table('sale_items')
                            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                            ->where('sales.sale_date', '>=', now()->subDays(6)->startOfDay())
                            ->selectRaw('sale_items.item_name, SUM(sale_items.qty) as qty_sold, SUM(sale_items.total) as revenue')
                            ->groupBy('sale_items.item_name')
                            ->orderByDesc('qty_sold')
                            ->limit(5)
                            ->get();
                    } catch (\Throwable $e) { return collect([]); }
                })(),

                // ----------------------------------------------------------------
                // Supplier dues (top 5 by amount, with credit_days + last purchase)
                // ----------------------------------------------------------------
                'supplierDues' => (function() {
                    try {
                        return \Illuminate\Support\Facades\DB::table('suppliers')
                            ->where('current_balance', '>', 0)
                            ->selectRaw('id, name, current_balance, credit_days')
                            ->addSelect(\Illuminate\Support\Facades\DB::raw(
                                '(SELECT MAX(purchase_date) FROM purchases WHERE purchases.supplier_id = suppliers.id) as last_purchase_date'
                            ))
                            ->orderByDesc('current_balance')
                            ->limit(5)
                            ->get();
                    } catch (\Throwable $e) {
                        // Fallback without subquery (if purchases table or column name differs)
                        try {
                            return \Illuminate\Support\Facades\DB::table('suppliers')
                                ->where('current_balance', '>', 0)
                                ->select('id', 'name', 'current_balance', 'credit_days')
                                ->orderByDesc('current_balance')
                                ->limit(5)
                                ->get();
                        } catch (\Throwable $e2) { return collect([]); }
                    }
                })(),
                'totalSupplierDues' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('suppliers')
                            ->where('current_balance', '>', 0)
                            ->sum('current_balance');
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'supplierDueCount' => (function() {
                    try {
                        return (int) \Illuminate\Support\Facades\DB::table('suppliers')
                            ->where('current_balance', '>', 0)
                            ->count();
                    } catch (\Throwable $e) { return 0; }
                })(),

                // ----------------------------------------------------------------
                // Godam stock levels
                // ----------------------------------------------------------------
                'godams' => (function() {
                    try {
                        return \Illuminate\Support\Facades\DB::table('godams')
                            ->where('is_active', true)
                            ->get();
                    } catch (\Throwable $e) { return collect([]); }
                })(),
                'godamStock' => (function() {
                    try {
                        return \Illuminate\Support\Facades\DB::table('godam_stock')
                            ->selectRaw('godam_id, SUM(quantity) as total_qty')
                            ->groupBy('godam_id')
                            ->get()
                            ->keyBy('godam_id');
                    } catch (\Throwable $e) { return collect([]); }
                })(),
                'shopFloorStock' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('items')
                            ->sum('on_hand');
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'totalStockValue' => (function() {
                    try {
                        return (float) \Illuminate\Support\Facades\DB::table('items')
                            ->selectRaw('SUM(on_hand * cost_rate) as value')
                            ->value('value') ?? 0.0;
                    } catch (\Throwable $e) { return 0.0; }
                })(),
                'salesTrend' => (function() {
                    try {
                        $startDate = now()->subDays(29)->startOfDay();
                        $rows = \Illuminate\Support\Facades\DB::table('sales')
                            ->selectRaw('DATE(sale_date) as date, payment_mode, SUM(grand_total) as total')
                            ->where('sale_date', '>=', $startDate)
                            ->groupByRaw('DATE(sale_date), payment_mode')
                            ->get();
                        
                        $dataByDate = [];
                        foreach ($rows as $row) {
                            $date = $row->date;
                            $mode = $row->payment_mode === 'Cash' ? 'cash' : 'credit';
                            $dataByDate[$date][$mode] = (float)$row->total;
                        }

                        $labels = [];
                        $cashData = [];
                        $creditData = [];
                        for ($i = 29; $i >= 0; $i--) {
                            $d = now()->subDays($i)->toDateString();
                            $labels[] = \Carbon\Carbon::parse($d)->format('d M');
                            $cashData[] = $dataByDate[$d]['cash'] ?? 0.0;
                            $creditData[] = $dataByDate[$d]['credit'] ?? 0.0;
                        }

                        return json_encode([
                            'labels' => $labels,
                            'cash' => $cashData,
                            'credit' => $creditData
                        ]);
                    } catch (\Throwable $e) {
                        return json_encode(['labels' => [], 'cash' => [], 'credit' => []]);
                    }
                })(),
                'paymentSplit' => (function() {
                    try {
                        $cashAmt = (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today())
                            ->where('payment_mode', 'Cash')
                            ->sum('grand_total');
                        
                        $creditAmt = (float) \Illuminate\Support\Facades\DB::table('sales')
                            ->whereDate('sale_date', today())
                            ->where('payment_mode', 'Debit')
                            ->sum('grand_total');
                        
                        $total = $cashAmt + $creditAmt;
                        if ($total > 0) {
                            $cashPct = round(($cashAmt / $total) * 100);
                            $creditPct = 100 - $cashPct;
                        } else {
                            $cashPct = 0;
                            $creditPct = 0;
                        }
                        
                        return json_encode([
                            'cash' => $cashPct,
                            'credit' => $creditPct,
                            'cashAmt' => $cashAmt,
                            'creditAmt' => $creditAmt
                        ]);
                    } catch (\Throwable $e) {
                        return json_encode([
                            'cash' => 0,
                            'credit' => 0,
                            'cashAmt' => 0.0,
                            'creditAmt' => 0.0
                        ]);
                    }
                })(),
                'topItemsChart' => (function() {
                    try {
                        $rows = \Illuminate\Support\Facades\DB::table('sale_items')
                            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                            ->where('sales.sale_date', '>=', now()->subDays(6)->startOfDay())
                            ->selectRaw('sale_items.item_name, SUM(sale_items.qty) as qty_sold')
                            ->groupBy('sale_items.item_name')
                            ->orderByDesc('qty_sold')
                            ->limit(5)
                            ->get();
                        
                        $labels = [];
                        $data = [];
                        foreach ($rows as $row) {
                            $labels[] = $row->item_name;
                            $data[] = (float)$row->qty_sold;
                        }
                        return json_encode([
                            'labels' => $labels,
                            'data' => $data
                        ]);
                    } catch (\Throwable $e) {
                        return json_encode(['labels' => [], 'data' => []]);
                    }
                })(),
                'categoryChart' => (function() {
                    try {
                        $rows = \Illuminate\Support\Facades\DB::table('sale_items')
                            ->join('items', 'sale_items.item_id', '=', 'items.id')
                            ->join('departments', 'items.department_id', '=', 'departments.id')
                            ->selectRaw('departments.name as name, SUM(sale_items.total) as revenue')
                            ->groupBy('departments.name')
                            ->orderByDesc('revenue')
                            ->get();
                        
                        $totalRevenue = $rows->sum('revenue');
                        if ($totalRevenue <= 0) {
                            return json_encode(['labels' => [], 'data' => []]);
                        }
                        
                        $labels = [];
                        $data = [];
                        $otherRevenue = 0.0;
                        
                        foreach ($rows as $index => $row) {
                            if ($index < 5) {
                                $labels[] = $row->name;
                                $data[] = (float)$row->revenue;
                            } else {
                                $otherRevenue += $row->revenue;
                            }
                        }
                        
                        if ($otherRevenue > 0) {
                            $labels[] = 'Other';
                            $data[] = (float)$otherRevenue;
                        }
                        
                        return json_encode([
                            'labels' => $labels,
                            'data' => $data
                        ]);
                    } catch (\Throwable $e) {
                        return json_encode(['labels' => [], 'data' => []]);
                    }
                })(),
                'customerDuesChart' => (function() {
                    try {
                        $rows = \Illuminate\Support\Facades\DB::table('customers')
                            ->where('balance', '>', 0)
                            ->orderByDesc('balance')
                            ->limit(5)
                            ->select('name', 'balance')
                            ->get();
                        
                        $labels = [];
                        $data = [];
                        foreach ($rows as $row) {
                            $labels[] = $row->name;
                            $data[] = (float)$row->balance;
                        }
                        return json_encode([
                            'labels' => $labels,
                            'data' => $data
                        ]);
                    } catch (\Throwable $e) {
                        return json_encode(['labels' => [], 'data' => []]);
                    }
                })(),
            ])
        );
    }
}

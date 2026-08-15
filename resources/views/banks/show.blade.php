@extends('layouts.admin')

@section('title', $bank->account_title . ' - Bank Statement')
@section('navbar_subtitle', 'Bank Account Statement')

@section('content')
<div class="space-y-6">

    {{-- ACCOUNTS MODULE SUB-NAVBAR --}}
    <div class="bg-slate-900/80 backdrop-blur-md p-2 rounded-2xl border border-slate-800 shadow-lg flex items-center justify-between gap-2">
        <div class="flex items-center gap-1">
            <a href="{{ route('banks.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-800 text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left"></i> Back to Banks & Cash
            </a>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-2">
            <i class="fas fa-print"></i> Print Statement
        </button>
    </div>

    {{-- STATEMENT HEADER CARD --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 shadow-xl flex flex-wrap justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-university"></i>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-white tracking-tight">{{ $bank->account_title }}</h2>
                <div class="flex items-center gap-3 text-xs text-slate-400 mt-1">
                    <span><i class="fas fa-landmark mr-1"></i>{{ $bank->bank_name }}</span>
                    <span><i class="fas fa-hashtag mr-1"></i>Acc No: {{ $bank->account_number ?: 'N/A' }}</span>
                    <span><i class="fas fa-code mr-1 font-mono"></i>GL: {{ $bank->gl_code }}</span>
                </div>
            </div>
        </div>

        <div class="bg-slate-950 px-5 py-3 rounded-2xl border border-slate-800 text-right">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Current Balance</span>
            <span class="text-2xl font-extrabold font-mono tracking-tight {{ $bank->current_balance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                Rs. {{ number_format($bank->current_balance, 2) }}
            </span>
        </div>
    </div>

    {{-- STATEMENT FILTER & TABLE --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden mb-12">
        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                <i class="fas fa-list-alt text-indigo-400"></i> Account Statement Transactions
            </h3>
            <span class="text-xs text-slate-500 font-mono">Opening Balance: Rs. {{ number_format($bank->opening_balance, 2) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="p-4">Date</th>
                        <th class="p-4">Ref No</th>
                        <th class="p-4">Description / Narration</th>
                        <th class="p-4 text-right">Deposit (In)</th>
                        <th class="p-4 text-right">Withdraw (Out)</th>
                        <th class="p-4 text-right">Running Balance (Rs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <tr class="bg-slate-950/40">
                        <td class="p-4 text-xs font-mono text-slate-500">-</td>
                        <td class="p-4 text-xs font-mono text-slate-500">-</td>
                        <td class="p-4 font-bold text-white text-xs">Opening Balance</td>
                        <td class="p-4 text-right font-mono text-slate-500">-</td>
                        <td class="p-4 text-right font-mono text-slate-500">-</td>
                        <td class="p-4 text-right font-mono font-bold text-emerald-400 text-xs">
                            Rs. {{ number_format($bank->opening_balance, 2) }}
                        </td>
                    </tr>

                    @php $running_balance = $bank->opening_balance; @endphp
                    @foreach([
                        ['date'=>'2026-01-20', 'ref'=>'SAL-101', 'desc'=>'Daily Cash Sales Deposit', 'in'=>50000, 'out'=>0],
                        ['date'=>'2026-01-21', 'ref'=>'CHQ-998', 'desc'=>'Payment to Supplier (Nestle)', 'in'=>0, 'out'=>12000],
                        ['date'=>'2026-01-22', 'ref'=>'TRF-001', 'desc'=>'Transfer from HBL', 'in'=>25000, 'out'=>0],
                    ] as $txn)
                    @php
                        $running_balance += $txn['in'] - $txn['out'];
                    @endphp
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="p-4 text-xs font-mono text-slate-300">{{ $txn['date'] }}</td>
                        <td class="p-4 text-xs font-mono font-bold text-indigo-400">{{ $txn['ref'] }}</td>
                        <td class="p-4 text-xs text-white">{{ $txn['desc'] }}</td>
                        <td class="p-4 text-right font-mono font-bold text-xs text-emerald-400">
                            {{ $txn['in'] > 0 ? 'Rs. ' . number_format($txn['in'], 2) : '-' }}
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-xs text-rose-400">
                            {{ $txn['out'] > 0 ? 'Rs. ' . number_format($txn['out'], 2) : '-' }}
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-xs text-white">
                            Rs. {{ number_format($running_balance, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold mb-4">Supplier Payments</h1>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                    <tr>
                        <th class="p-2">Date</th>
                        <th class="p-2">Amount</th>
                        <th class="p-2">Method</th>
                        <th class="p-2">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-2">{{ $pay->payment_date->format('d-M-Y') }}</td>
                            <td class="p-2 font-medium">Rs. {{ number_format($pay->amount, 2) }}</td>
                            <td class="p-2 capitalize">{{ str_replace('_', ' ', $pay->payment_method) }}</td>
                            <td class="p-2">{{ $pay->reference_note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-slate-450 dark:text-slate-500">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

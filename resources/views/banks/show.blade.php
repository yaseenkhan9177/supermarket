<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bank->account_title }} - Statement | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-emerald-800 border-b border-emerald-700 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="/banks" class="text-emerald-200 hover:text-white transition">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none">
                        {{ $bank->account_title }}
                    </h1>
                    <span class="text-xs text-emerald-200 font-medium mt-0.5">
                        {{ $bank->bank_name }} | {{ $bank->account_number }}
                    </span>
                </div>
            </div>
            <div class="bg-emerald-900/50 px-4 py-2 rounded-lg border border-emerald-600">
                <span class="text-xs text-emerald-300 uppercase font-bold block">Current Balance</span>
                <span class="text-xl font-mono font-bold text-white">Rs. {{ number_format($bank->current_balance, 2) }}</span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <div class="bg-white p-4 rounded-xl shadow mb-6 flex justify-between items-center">
            <div class="flex gap-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Start Date</label>
                    <input type="date" class="border border-gray-300 rounded p-1 text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">End Date</label>
                    <input type="date" class="border border-gray-300 rounded p-1 text-sm">
                </div>
                <button class="bg-emerald-600 text-white px-4 py-1 rounded text-sm font-bold mt-4">Filter</button>
            </div>
            <button class="text-emerald-600 hover:text-emerald-800 font-bold text-sm">
                <i class="fas fa-print mr-1"></i> Print Statement
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase">
                        <th class="p-4 w-32">Date</th>
                        <th class="p-4 w-32">Ref No</th>
                        <th class="p-4">Description / Narration</th>
                        <th class="p-4 w-32 text-right text-green-600">Deposit (In)</th>
                        <th class="p-4 w-32 text-right text-red-600">Withdraw (Out)</th>
                        <th class="p-4 w-40 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="bg-yellow-50">
                        <td class="p-4 text-xs font-mono text-gray-500">-</td>
                        <td class="p-4 text-xs font-mono text-gray-500">-</td>
                        <td class="p-4 font-bold text-gray-700">Opening Balance</td>
                        <td class="p-4 text-right font-mono text-gray-400">-</td>
                        <td class="p-4 text-right font-mono text-gray-400">-</td>
                        <td class="p-4 text-right font-mono font-bold text-gray-800">{{ number_format($bank->opening_balance, 2) }}</td>
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
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 text-sm text-gray-600">{{ $txn['date'] }}</td>
                        <td class="p-4 text-xs font-mono text-blue-600">{{ $txn['ref'] }}</td>
                        <td class="p-4 text-sm font-medium text-gray-800">{{ $txn['desc'] }}</td>

                        <td class="p-4 text-right font-mono text-sm {{ $txn['in'] > 0 ? 'text-green-600 font-bold' : 'text-gray-300' }}">
                            {{ $txn['in'] > 0 ? number_format($txn['in'], 2) : '-' }}
                        </td>

                        <td class="p-4 text-right font-mono text-sm {{ $txn['out'] > 0 ? 'text-red-600 font-bold' : 'text-gray-300' }}">
                            {{ $txn['out'] > 0 ? number_format($txn['out'], 2) : '-' }}
                        </td>

                        <td class="p-4 text-right font-mono font-bold text-gray-900 bg-gray-50">
                            {{ number_format($running_balance, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>
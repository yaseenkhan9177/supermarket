<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refunds List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-900 font-sans text-gray-200">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-undo-alt text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-red-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Refunds History</span>
                </div>
            </div>
            <div>
                <a href="/admin" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px]">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">Refunds History</h2>
            <a href="{{ route('refunds.create') }}" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded shadow transition">
                <i class="fas fa-plus mr-2"></i> New Refund
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                            <th class="p-4">Credit No</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Salesman</th>
                            <th class="p-4 text-right">Total Amount</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $refund)
                        <tr class="border-b hover:bg-red-50 transition">
                            <td class="p-4 font-mono font-bold text-red-600">{{ $refund->credit_no }}</td>
                            <td class="p-4">{{ $refund->refund_date }}</td>
                            <td class="p-4">{{ $refund->customer->name ?? 'Walk-in' }}</td>
                            <td class="p-4">{{ $refund->salesman->name ?? '-' }}</td>
                            <td class="p-4 text-right font-bold">{{ number_format($refund->total_amount, 2) }}</td>
                            <td class="p-4 text-center">
                                <a href="{{ route('refunds.print', $refund->id) }}" class="text-gray-500 hover:text-red-600 transition" title="Print Receipt">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">No refunds found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($refunds->hasPages())
            <div class="p-4 bg-gray-50 border-t">
                {{ $refunds->links() }}
            </div>
            @endif
        </div>

    </div>

</body>

</html>
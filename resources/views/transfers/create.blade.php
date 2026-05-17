<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="transferForm()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-exchange-alt text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-purple-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Internal Money Transfer</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px]">

        {{-- Error/Success Alerts --}}
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Success</p>
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-purple-100 border-l-4 border-purple-500 text-purple-700 p-4 mb-4" role="alert">
            <p class="font-bold">Validation Error</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="/transfers/store" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-4 bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-700 pb-2">
                        <i class="fas fa-info-circle text-purple-400"></i>
                        <h3 class="text-white font-bold">Transaction Info</h3>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Transfer No</label>
                            <input type="text" name="transfer_no" value="TRF-{{ date('Ymd') }}-{{ rand(10,99) }}" readonly class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-sm font-mono text-purple-400">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Date</label>
                            <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-sm text-gray-200">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Operator / Salesman</label>
                            <select name="user_id" class="w-full bg-gray-900 border border-gray-600 rounded p-3 text-sm text-gray-200">
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 bg-white p-8 rounded-xl text-gray-800 shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-500 via-purple-500 to-green-500"></div>

                    <h3 class="font-bold text-xl text-gray-900 mb-8 text-center">Fund Movement Details</h3>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">

                        <div class="flex-1 w-full p-5 bg-red-50 border border-red-100 rounded-xl text-center relative group hover:shadow-md transition">
                            <div class="w-12 h-12 mx-auto bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-3 text-xl">
                                <i class="fas fa-university"></i>
                            </div>
                            <label class="block text-xs font-bold text-red-400 uppercase mb-2">Withdraw From</label>
                            <select name="from_account" class="w-full bg-white border border-red-200 rounded p-2 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                                <option>010001: MAIN SAFE</option>
                                <option>020001: MEEZAN BANK</option>
                                <option>020002: HBL BANK</option>
                            </select>
                        </div>

                        <div class="text-purple-300 text-2xl animate-pulse">
                            <i class="fas fa-chevron-right hidden md:block"></i>
                            <i class="fas fa-chevron-down block md:hidden"></i>
                        </div>

                        <div class="flex-1 w-full p-5 bg-green-50 border border-green-100 rounded-xl text-center relative group hover:shadow-md transition">
                            <div class="w-12 h-12 mx-auto bg-green-100 text-green-500 rounded-full flex items-center justify-center mb-3 text-xl">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <label class="block text-xs font-bold text-green-400 uppercase mb-2">Deposit To</label>
                            <select name="to_account" class="w-full bg-white border border-green-200 rounded p-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                                <option>010000: CASH DRAWER 1</option>
                                <option>010002: PETTY CASH</option>
                                <option>010003: CASH DRAWER 2</option>
                            </select>
                        </div>

                    </div>

                    <div class="max-w-md mx-auto">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1 text-center">Amount to Transfer</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-400 text-lg">PKR</span>
                            <input type="number" step="0.01" name="amount" placeholder="0.00" class="w-full pl-14 p-3 border-2 border-purple-100 rounded-xl text-center text-2xl font-bold text-purple-600 focus:border-purple-500 focus:outline-none transition">
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Purpose / Memo</label>
                        <input type="text" name="purpose" placeholder="e.g. Daily cash refill for counter 1" class="w-full bg-gray-50 border border-gray-200 rounded p-3 text-sm">
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" class="px-6 py-3 bg-gray-100 text-gray-600 font-bold rounded-lg hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-purple-600 text-white font-bold rounded-lg shadow hover:bg-purple-700 transition transform hover:-translate-y-1">
                            <i class="fas fa-check-circle mr-2"></i> Confirm Transfer
                        </button>
                    </div>

                </div>
            </div>

        </form>
    </div>

    <script>
        function transferForm() {
            return {
                // Alpine logic if needed later
            }
        }
    </script>
</body>

</html>
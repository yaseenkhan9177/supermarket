<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Report | OwnStore PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="bg-white rounded-xl shadow-2xl border border-teal-100 max-w-lg w-full overflow-hidden">

            <div class="bg-teal-900 px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-teal-700 flex items-center justify-center text-white text-sm">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Report Setup</h2>
                        <p class="text-teal-300 text-xs">Configure parameters</p>
                    </div>
                </div>
                <a href="/reports" class="text-teal-300 hover:text-white"><i class="fas fa-times"></i></a>
            </div>

            <form action="/reports/generate" method="POST" target="_blank" class="p-8 space-y-6">
                @csrf
                <input type="hidden" name="report_id" value="{{ $report_id }}">

                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">{{ $report_name ?? 'Sales Report' }}</h3>
                    <p class="text-gray-500 text-sm">Select the date range for analysis</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ date('Y-m-01') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-teal-500 outline-none text-gray-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-teal-500 outline-none text-gray-700 font-medium">
                    </div>
                </div>

                @if(str_contains(strtolower($report_name ?? ''), 'sales'))
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter by User (Optional)</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-lg p-3 bg-white">
                        <option value="">All Users</option>
                    </select>
                </div>
                @endif

                <div class="pt-4 flex gap-4">
                    <button type="submit" name="format" value="pdf" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-lg shadow transition flex justify-center items-center gap-2">
                        <i class="fas fa-file-pdf"></i> Generate PDF
                    </button>
                    <button type="submit" name="format" value="excel" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow transition flex justify-center items-center gap-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </form>

        </div>
    </div>

</body>

</html>
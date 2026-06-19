<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Entry | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="journalForm()">

    <nav class="bg-gray-900 border-b border-gray-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white shadow-md border border-gray-600">
                    <i class="fas fa-book text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-gray-400">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-400 font-medium mt-0.5">General Journal (JV)</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-gray-700">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <form action="/journals/store" method="POST" @submit.prevent="validateAndSubmit">
            @csrf

            <div class="bg-white p-6 rounded-xl border border-gray-300 shadow-sm mb-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-gray-600"></i>
                        <h3 class="font-bold text-lg">Entry Details</h3>
                    </div>
                    <div class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded">
                        JV-{{ date('Y') }}-AUTO
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Journal Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-gray-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reference / Memo</label>
                        <input type="text" name="memo" placeholder="e.g. Monthly Depreciation Adjustment" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-gray-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-300 mb-6">
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700"><i class="fas fa-list-ol mr-2"></i>Ledger Entries</h3>
                    <button type="button" @click="addRow()" class="bg-gray-800 text-white hover:bg-black px-4 py-2 rounded text-sm font-bold transition shadow">
                        <i class="fas fa-plus mr-1"></i> Add Line
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-100 border-b border-gray-200">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3 w-64">Account</th>
                                <th class="p-3">Description (Line)</th>
                                <th class="p-3 w-32 text-right">Debit</th>
                                <th class="p-3 w-32 text-right">Credit</th>
                                <th class="p-3 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition group">
                                    <td class="p-3 text-center text-gray-400 font-mono text-xs" x-text="index + 1"></td>

                                    <td class="p-3">
                                        <select :name="`entries[${index}][account_code]`" x-model="row.account_code" class="w-full border border-gray-300 rounded p-2 text-sm bg-white focus:border-gray-500 outline-none">
                                            <option value="">-- Select Account --</option>
                                            <optgroup label="Assets">
                                                <option value="1010">Cash on Hand</option>
                                                <option value="1020">Bank - Meezan</option>
                                                <option value="1200">Accounts Receivable</option>
                                            </optgroup>
                                            <optgroup label="Liabilities">
                                                <option value="2010">Accounts Payable</option>
                                            </optgroup>
                                            <optgroup label="Expenses">
                                                <option value="5010">Rent Expense</option>
                                                <option value="5020">Electricity Expense</option>
                                            </optgroup>
                                        </select>
                                        <input type="hidden" :name="`entries[${index}][account_name]`" :value="getAccountName(row.account_code)">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" :name="`entries[${index}][description]`" x-model="row.description" placeholder="Optional detail..." class="w-full border-0 bg-transparent focus:ring-0 text-sm placeholder-gray-300">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" :name="`entries[${index}][debit]`" x-model="row.debit" @input="row.credit = 0" class="w-full border border-gray-300 rounded p-2 text-right font-mono text-sm focus:border-gray-600 focus:bg-gray-50 transition">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" :name="`entries[${index}][credit]`" x-model="row.credit" @input="row.debit = 0" class="w-full border border-gray-300 rounded p-2 text-right font-mono text-sm focus:border-gray-600 focus:bg-gray-50 transition">
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-gray-300 hover:text-red-500 transition" title="Remove Line">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>

                        <tfoot class="bg-gray-100 font-bold text-gray-700">
                            <tr>
                                <td colspan="3" class="p-3 text-right uppercase text-xs">Total</td>
                                <td class="p-3 text-right font-mono" :class="totalDebit == totalCredit ? 'text-green-600' : 'text-red-600'">
                                    <span x-text="totalDebit.toFixed(2)"></span>
                                </td>
                                <td class="p-3 text-right font-mono" :class="totalDebit == totalCredit ? 'text-green-600' : 'text-red-600'">
                                    <span x-text="totalCredit.toFixed(2)"></span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div x-show="totalDebit !== totalCredit" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-20 animate-pulse">
                <strong class="font-bold">Entry Unbalanced!</strong>
                <span class="block sm:inline"> The Debit and Credit totals must match. Difference: <span x-text="(totalDebit - totalCredit).toFixed(2)"></span></span>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-end gap-4">
                    <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">Cancel</button>

                    <button type="submit"
                        :disabled="totalDebit !== totalCredit || totalDebit == 0"
                        :class="(totalDebit !== totalCredit || totalDebit == 0) ? 'bg-gray-400 cursor-not-allowed' : 'bg-gray-900 hover:bg-black shadow-lg hover:-translate-y-1'"
                        class="px-8 py-3 text-white font-bold rounded transition transform flex items-center gap-2">
                        <i class="fas fa-save"></i> Post Entry
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Standalone Script for Flash Messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessionSuccess = "{{ session('success') }}";
            const sessionError = "{{ session('error') }}";

            if (sessionSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: sessionSuccess,
                    confirmButtonColor: '#1f2937'
                });
            }

            if (sessionError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: sessionError,
                    confirmButtonColor: '#1f2937'
                });
            }
        });
    </script>

    <script>
        function journalForm() {
            return {
                rows: [{
                        account_code: '',
                        description: '',
                        debit: 0,
                        credit: 0
                    },
                    {
                        account_code: '',
                        description: '',
                        debit: 0,
                        credit: 0
                    } // Start with 2 rows usually
                ],

                addRow() {
                    this.rows.push({
                        account_code: '',
                        description: '',
                        debit: 0,
                        credit: 0
                    });
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                get totalDebit() {
                    return this.rows.reduce((sum, row) => sum + parseFloat(row.debit || 0), 0);
                },

                get totalCredit() {
                    return this.rows.reduce((sum, row) => sum + parseFloat(row.credit || 0), 0);
                },

                getAccountName(code) {
                    // Helper to send name to backend without needing extra DB lookup on create
                    // In real app, this map would be populated from backend
                    const map = {
                        '1010': 'Cash on Hand',
                        '1020': 'Bank - Meezan',
                        '1200': 'Accounts Receivable',
                        '2010': 'Accounts Payable',
                        '5010': 'Rent Expense',
                        '5020': 'Electricity Expense'
                    };
                    return map[code] || 'Unknown';
                },

                validateAndSubmit(e) {
                    if (this.totalDebit !== this.totalCredit) {
                        Swal.fire('Error', 'Journal is not balanced!', 'error');
                        return;
                    }
                    if (this.totalDebit === 0) {
                        Swal.fire('Error', 'Amounts cannot be zero', 'error');
                        return;
                    }
                    e.target.submit();
                }
            }
        }
    </script>
</body>

</html>
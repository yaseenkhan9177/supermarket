@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto" x-data="{ showModal: false, editMode: false, form: { id: null, name: '', company: '', phone: '', address: '', opening_balance: '' } }">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="flex flex-col justify-center">
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">Suppliers</h1>
            <p class="text-slate-500 text-sm mb-4">Manage vendors and track payables.</p>
            <button @click="editMode = false; form = {id: null, name:'', company:'', phone:'', address:'', opening_balance:''}; showModal = true"
                class="w-fit px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg flex items-center gap-2">
                <i class="fas fa-plus"></i> Add Supplier
            </button>
        </div>

        <div class="bg-gradient-to-br from-red-600 to-red-800 rounded-2xl p-6 text-white shadow-xl">
            <p class="text-red-200 font-bold uppercase text-xs">Total Payable (Debt)</p>
            <h2 class="text-3xl font-extrabold mt-2">Rs. {{ number_format($totalPayable, 2) }}</h2>
            <p class="text-xs mt-2 opacity-80">Money we owe to suppliers</p>
        </div>

        <div class="bg-gradient-to-br from-green-600 to-green-800 rounded-2xl p-6 text-white shadow-xl">
            <p class="text-green-200 font-bold uppercase text-xs">Total Advance</p>
            <h2 class="text-3xl font-extrabold mt-2">Rs. {{ number_format(abs($totalAdvance), 2) }}</h2>
            <p class="text-xs mt-2 opacity-80">Money paid in advance</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                <tr>
                    <th class="p-4">Supplier Details</th>
                    <th class="p-4">Contact</th>
                    <th class="p-4 text-right">Balance</th>
                    <th class="p-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($suppliers as $supplier)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="p-4">
                        <span class="block font-bold text-slate-800 dark:text-white text-base">{{ $supplier->name }}</span>
                        <span class="text-xs text-slate-500">{{ $supplier->company_name ?? 'Individual' }}</span>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-slate-600 dark:text-slate-300"><i class="fas fa-phone text-xs w-4"></i> {{ $supplier->phone ?? '-' }}</span>
                            <span class="text-slate-500 text-xs truncate max-w-[150px]"><i class="fas fa-map-marker-alt text-xs w-4"></i> {{ $supplier->address ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="p-4 text-right">
                        @if($supplier->balance > 0)
                        <span class="font-bold text-red-500 bg-red-100 px-2 py-1 rounded text-xs">Payable</span>
                        <div class="font-bold text-red-600 mt-1">Rs. {{ number_format($supplier->balance, 2) }}</div>
                        @elseif($supplier->balance < 0)
                            <span class="font-bold text-green-600 bg-green-100 px-2 py-1 rounded text-xs">Advance</span>
                            <div class="font-bold text-green-600 mt-1">Rs. {{ number_format(abs($supplier->balance), 2) }}</div>
                            @else
                            <span class="font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded text-xs">Settled</span>
                            <div class="font-bold text-slate-800 mt-1">-</div>
                            @endif
                    </td>
                    <td class="p-4 text-center">
                        <button @click="editMode = true; form = { id: {{ $supplier->id }}, name: '{{ $supplier->name }}', company: '{{ $supplier->company_name }}', phone: '{{ $supplier->phone }}', address: '{{ $supplier->address }}' }; showModal = true"
                            class="text-blue-500 hover:text-blue-700 mx-1"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $suppliers->links() }}
        </div>
    </div>

    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-700">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4" x-text="editMode ? 'Edit Supplier' : 'Add New Supplier'"></h2>

            <form method="POST" :action="editMode ? '/suppliers/' + form.id : '/suppliers'">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Name</label>
                        <input type="text" name="name" x-model="form.name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Company (Optional)</label>
                        <input type="text" name="company_name" x-model="form.company" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Phone</label>
                        <input type="text" name="phone" x-model="form.phone" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Address</label>
                        <input type="text" name="address" x-model="form.address" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2 text-white">
                    </div>

                    <template x-if="!editMode">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Opening Balance (Debt)</label>
                            <input type="number" name="opening_balance" x-model="form.opening_balance" placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2 text-white">
                            <p class="text-[10px] text-slate-400 mt-1">Enter positive amount if you owe them money.</p>
                        </div>
                    </template>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showModal = false" class="flex-1 py-2 rounded-lg border border-slate-600 text-slate-400 font-bold">Cancel</button>
                    <button type="submit" class="flex-1 py-2 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-500">Save</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
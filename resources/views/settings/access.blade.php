@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 font-sans text-sm relative">

    <!-- 1. Header & Selector -->
    <div class="bg-white px-8 py-6 shadow-sm border-b border-gray-200">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Role Permissions</h1>
                <p class="text-gray-500 text-xs mt-1">Manage access levels and feature authorities for system users.</p>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1">Select Role to Edit</label>
                    <div class="relative w-64">
                        <select class="block appearance-none w-full bg-gray-50 border border-gray-300 hover:border-indigo-400 px-4 py-2 pr-8 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 leading-tight transition-colors">
                            <option value="ADMIN">ADMIN</option>
                            <option value="MANAGER">MANAGER</option>
                            <option value="CASHIER">CASHIER</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex space-x-6 text-xs font-medium pt-2">
                <div class="flex items-center space-x-2">
                    <label class="text-gray-600">Show Agent:</label>
                    <select class="border-b border-gray-300 bg-transparent focus:border-indigo-500 focus:outline-none py-1 min-w-[100px]">
                        <option>None</option>
                    </select>
                </div>
                <a href="#" class="text-indigo-600 hover:text-indigo-800 transition-colors flex items-center">
                    <i class="fas fa-key mr-1"></i> Change Password
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Content Area (The Grid) -->
    <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-1 xl:grid-cols-2 gap-8">

        <!-- Card 1: Sales & Transactions -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transform transition hover:shadow-lg duration-300">
            <div class="bg-gradient-to-r from-blue-50 to-white px-6 py-4 border-b border-blue-100 flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg mr-3 text-blue-600">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Sales & Transactions</h2>
            </div>

            <div class="p-6">
                <!-- Grid Header -->
                <div class="grid grid-cols-12 gap-2 mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider text-center border-b border-gray-100 pb-2">
                    <div class="col-span-4 text-left pl-2">Feature Name</div>
                    <div class="col-span-2">View</div>
                    <div class="col-span-2">Add</div>
                    <div class="col-span-2">Edit</div>
                    <div class="col-span-2">Delete</div>
                </div>

                <!-- Rows -->
                <div class="space-y-3">
                    @foreach(['CASH Sales', 'DEBT Sales', 'CASH Returns', 'CRDT Returns', 'CASH Bill', 'CRDT Bill', 'Stock Transfer'] as $label)
                    <div class="grid grid-cols-12 gap-2 items-center text-xs py-2 hover:bg-gray-50 rounded-lg transition-colors border-b border-gray-50 last:border-0 border-dashed">
                        <div class="col-span-4 font-semibold text-gray-700 pl-2">{{ $label }}</div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-indigo-600 rounded-sm border-gray-300 h-4 w-4 focus:ring-indigo-500 transition duration-150 ease-in-out"></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-indigo-600 rounded-sm border-gray-300 h-4 w-4 focus:ring-indigo-500 transition duration-150 ease-in-out"></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-indigo-600 rounded-sm border-gray-300 h-4 w-4 focus:ring-indigo-500 transition duration-150 ease-in-out"></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-red-500 rounded-sm border-gray-300 h-4 w-4 focus:ring-red-500 transition duration-150 ease-in-out"></div>
                    </div>
                    @endforeach
                </div>

                <!-- Sub-section: Counter Controls -->
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Counter Controls</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="text-indigo-600 rounded border-gray-300">
                            <span class="text-gray-600">Counter Sales</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="text-indigo-600 rounded border-gray-300">
                            <span class="text-gray-600">Counter Returns</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="text-indigo-600 rounded border-gray-300">
                            <span class="text-gray-600">Change Discounts</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="text-indigo-600 rounded border-gray-300">
                            <span class="text-gray-600">Can Close Session</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>


        <!-- Card 2: Financials & Accounts -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transform transition hover:shadow-lg duration-300">
            <div class="bg-gradient-to-r from-emerald-50 to-white px-6 py-4 border-b border-emerald-100 flex items-center">
                <div class="bg-emerald-100 p-2 rounded-lg mr-3 text-emerald-600">
                    <i class="fas fa-coins"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Financials & Accounts</h2>
            </div>
            <div class="p-6">
                <!-- Grid Header -->
                <div class="grid grid-cols-12 gap-2 mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider text-center border-b border-gray-100 pb-2">
                    <div class="col-span-4 text-left pl-2">Feature Name</div>
                    <div class="col-span-2">View</div>
                    <div class="col-span-2">Add</div>
                    <div class="col-span-2">Edit</div>
                    <div class="col-span-2">Delete</div>
                </div>

                <!-- Rows -->
                <div class="space-y-3">
                    @foreach(['Receipts', 'Payments', 'Journals', 'Adjust Stock', 'Items Management', 'Accounts', 'Banks'] as $label)
                    <div class="grid grid-cols-12 gap-2 items-center text-xs py-2 hover:bg-gray-50 rounded-lg transition-colors border-b border-gray-50 last:border-0 border-dashed">
                        <div class="col-span-4 font-semibold text-gray-700 pl-2">{{ $label }}</div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-emerald-600 rounded-sm border-gray-300 h-4 w-4 focus:ring-emerald-500"></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-emerald-600 rounded-sm border-gray-300 h-4 w-4 focus:ring-emerald-500"></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-emerald-600 rounded-sm border-gray-300 h-4 w-4 focus:ring-emerald-500"></div>
                        <div class="col-span-2 flex justify-center"><input type="checkbox" class="form-checkbox text-red-500 rounded-sm border-gray-300 h-4 w-4 focus:ring-red-500"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Card 3: Administrative Control (Spans Full Width on large) -->
        <div class="xl:col-span-2 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden transform transition hover:shadow-lg duration-300">
            <div class="bg-gradient-to-r from-purple-50 to-white px-6 py-4 border-b border-purple-100 flex items-center">
                <div class="bg-purple-100 p-2 rounded-lg mr-3 text-purple-600">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Administrative Control</h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <!-- Reporting -->
                    <div>
                        <h3 class="text-xs font-bold text-purple-900 uppercase tracking-widest mb-3 border-b border-purple-100 pb-1">Reporting</h3>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Print Reports</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Create Statements</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Check Registers</span>
                            </label>
                        </div>
                    </div>

                    <!-- Data Management -->
                    <div>
                        <h3 class="text-xs font-bold text-purple-900 uppercase tracking-widest mb-3 border-b border-purple-100 pb-1">Data Mgmt</h3>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Create Backups</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Restore Data</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Reconcile Banks</span>
                            </label>
                        </div>
                    </div>

                    <!-- System Users -->
                    <div>
                        <h3 class="text-xs font-bold text-purple-900 uppercase tracking-widest mb-3 border-b border-purple-100 pb-1">System Users</h3>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Add/Delete Users</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Add/Change GLs</span>
                            </label>
                        </div>
                    </div>

                    <!-- Warehouse/Branch -->
                    <div>
                        <h3 class="text-xs font-bold text-purple-900 uppercase tracking-widest mb-3 border-b border-purple-100 pb-1">Warehouse</h3>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Add/Finalize DO</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Invoice to Branch</span>
                            </label>
                            <label class="flex items-center space-x-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">Purchase for WH</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- 3. Sticky Action Bar -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-lg z-50">
        <div class="max-w-7xl mx-auto flex justify-end items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-lg text-gray-500 font-medium hover:bg-gray-100 transition-colors">
                Cancel
            </a>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg transition-all flex items-center transform hover:-translate-y-0.5">
                <i class="fas fa-check-circle mr-2"></i>
                Save Changes
            </button>
        </div>
    </div>

</div>
@endsection
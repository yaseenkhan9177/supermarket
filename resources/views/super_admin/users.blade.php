@extends('super_admin.layout')

@section('title', 'Manage Admins')
@section('header', 'Manage Admins')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <div class="relative">
            <input type="text" placeholder="Search users..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
        </div>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-2"></i> Add New Admin
        </button>
    </div>
    <table class="w-full text-left whitespace-nowrap">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b">
                <th class="px-6 py-4">Name</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Role</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900">{{ Auth::guard('super_admin')->user()->name }}</td>
                <td class="px-6 py-4 text-gray-500">{{ Auth::guard('super_admin')->user()->email }}</td>
                <td class="px-6 py-4"><span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs">Super Admin</span></td>
                <td class="px-6 py-4"><span class="text-green-600 text-sm font-medium">Active</span></td>
                <td class="px-6 py-4 text-right text-gray-400">
                    <button class="hover:text-indigo-600 mr-2"><i class="fas fa-edit"></i></button>
                </td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900">Support Team</td>
                <td class="px-6 py-4 text-gray-500">support@ownstore.com</td>
                <td class="px-6 py-4"><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Support</span></td>
                <td class="px-6 py-4"><span class="text-green-600 text-sm font-medium">Active</span></td>
                <td class="px-6 py-4 text-right text-gray-400">
                    <button class="hover:text-indigo-600 mr-2"><i class="fas fa-edit"></i></button>
                    <button class="hover:text-red-600"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
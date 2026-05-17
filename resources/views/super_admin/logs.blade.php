@extends('super_admin.layout')

@section('title', 'System Logs')
@section('header', 'System Logs')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-800">Recent System Events</h3>
        <div class="flex space-x-2">
            <button class="px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">Export CSV</button>
            <button class="px-3 py-1 text-sm text-red-600 bg-red-50 rounded-md hover:bg-red-100">Clear Logs</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Timestamp</th>
                    <th class="px-6 py-3">Level</th>
                    <th class="px-6 py-3">Event</th>
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs">2026-01-21 10:15:22</td>
                    <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">INFO</span></td>
                    <td class="px-6 py-4 font-medium text-gray-900">Store Approved</td>
                    <td class="px-6 py-4">Super Admin</td>
                    <td class="px-6 py-4">Approved store "Smart Mart" (ID: 15)</td>
                </tr>
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs">2026-01-21 10:15:25</td>
                    <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">DEBUG</span></td>
                    <td class="px-6 py-4 font-medium text-gray-900">DB Created</td>
                    <td class="px-6 py-4">System</td>
                    <td class="px-6 py-4">Created database 'store_xyz_15'</td>
                </tr>
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs">2026-01-21 10:15:28</td>
                    <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">INFO</span></td>
                    <td class="px-6 py-4 font-medium text-gray-900">Migration Run</td>
                    <td class="px-6 py-4">System</td>
                    <td class="px-6 py-4">Completed 12 migrations for store_xyz_15</td>
                </tr>
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs">2026-01-20 14:30:11</td>
                    <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-800">WARNING</span></td>
                    <td class="px-6 py-4 font-medium text-gray-900">Suspicious Login</td>
                    <td class="px-6 py-4">Unknown</td>
                    <td class="px-6 py-4">Failed login attempt for admin from 192.168.1.5</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
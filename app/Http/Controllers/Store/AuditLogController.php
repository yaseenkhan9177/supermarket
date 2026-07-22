<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display centralized audit log stream (Owner / Admin only).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $isAdmin = ($user->role === 'owner' || $user->role === 'admin') 
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['owner', 'admin', 'Store Admin', 'Owner', 'manager']));

        if (!$isAdmin) {
            abort(403, 'Unauthorized access to Audit Log.');
        }

        $query = AuditLog::with('performer')->orderBy('created_at', 'desc');

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('performed_by')) {
            $query->where('performed_by', $request->performed_by);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(20)->withQueryString();

        $actionTypes = AuditLog::select('action_type')
            ->distinct()
            ->pluck('action_type');

        $users = User::select('id', 'name')->get();

        return view('reports.audit_log', compact('logs', 'actionTypes', 'users'));
    }
}

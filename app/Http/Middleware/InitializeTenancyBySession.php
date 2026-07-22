<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class InitializeTenancyBySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip initialization for super admin routes and central auth routes
        // (register/login/logout must always run against the central DB)
        if ($request->is('super*') || $request->is('register', 'login', 'logout')) {
            return $next($request);
        }

        $tenantId = null;

        // 1. Check if there is an active tenant in the session
        if (session()->has('tenant_id')) {
            $tenantId = session('tenant_id');
        } elseif (auth()->check()) {
            // Fallback: If user is authenticated, retrieve their tenant_id
            $user = auth()->user();
            if (isset($user->tenant_id) && $user->tenant_id) {
                $tenantId = $user->tenant_id;
                session(['tenant_id' => $tenantId]);
            }
        } elseif (auth('employee')->check()) {
            // Fallback for legacy employee guard
            $employee = auth('employee')->user();
            if (isset($employee->tenant_id) && $employee->tenant_id) {
                $tenantId = $employee->tenant_id;
                session(['tenant_id' => $tenantId]);
            }
        }

        // 2. Initialize tenancy if a tenant ID is resolved
        if ($tenantId) {
            if (!tenancy()->initialized || tenancy()->tenant->id !== $tenantId) {
                if (tenancy()->initialized) {
                    tenancy()->end();
                }

                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    if ($tenant->status === 'suspended') {
                        auth()->logout();
                        auth('employee')->logout();
                        session()->forget('tenant_id');
                        return redirect()->route('login')->withErrors(['email' => 'Your store has been suspended. Please contact support.']);
                    }
                    
                    tenancy()->initialize($tenant);
                } else {
                    // Tenant not found
                    auth()->logout();
                    auth('employee')->logout();
                    session()->forget('tenant_id');
                    return redirect()->route('login')->withErrors(['email' => 'Your store could not be resolved.']);
                }
            }
        }

        return $next($request);
    }
}

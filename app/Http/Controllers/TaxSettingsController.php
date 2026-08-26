<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\TaxSettingsHistory;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxSettingsController extends Controller
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Display the Store Admin Tax Settings page.
     */
    public function index()
    {
        $settings = CompanySetting::firstOrNew(['id' => 1]);
        $history = TaxSettingsHistory::with('user')
            ->latest()
            ->paginate(15);

        return view('settings.tax', compact('settings', 'history'));
    }

    /**
     * Update Store Admin Tax Settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'tax_enabled' => 'required|boolean',
            'tax_rate'    => 'required|numeric|min:0|max:100',
        ], [
            'tax_enabled.required' => 'Tax status is required.',
            'tax_rate.required'    => 'Tax rate percentage is required.',
            'tax_rate.numeric'     => 'Tax rate must be a valid numeric percentage.',
            'tax_rate.min'         => 'Tax rate cannot be negative.',
            'tax_rate.max'         => 'Tax rate cannot exceed 100%.',
        ]);

        $settings = CompanySetting::firstOrNew(['id' => 1]);

        $prevTaxEnabled = (bool) ($settings->tax_enabled ?? false);
        $prevTaxRate    = (float) ($settings->tax_rate ?? 0.00);

        $newTaxEnabled  = (bool) $validated['tax_enabled'];
        $newTaxRate     = round((float) $validated['tax_rate'], 2);

        $user = Auth::user();

        // Update settings record and mark as configured
        $settings->tax_enabled       = $newTaxEnabled;
        $settings->tax_rate          = $newTaxRate;
        $settings->tax_configured_at = now();
        $settings->tax_configured_by = $user?->id;
        $settings->save();

        // Record in dedicated tax settings audit history
        TaxSettingsHistory::create([
            'user_id'              => $user?->id,
            'user_name'            => $user?->name ?? 'Store Admin',
            'previous_tax_enabled' => $prevTaxEnabled,
            'new_tax_enabled'      => $newTaxEnabled,
            'previous_tax_rate'    => $prevTaxRate,
            'new_tax_rate'         => $newTaxRate,
            'ip_address'           => $request->ip(),
        ]);

        // Record in central AuditLog
        $statusStr = $newTaxEnabled ? 'Enabled (' . $newTaxRate . '%)' : 'Disabled';
        $prevStr   = $prevTaxEnabled ? 'Enabled (' . $prevTaxRate . '%)' : 'Disabled';
        AuditLog::record(
            'tax_settings.updated',
            "Tax settings updated by {$user?->name}: {$prevStr} → {$statusStr}",
            'CompanySetting',
            $settings->id,
            [
                'previous' => ['tax_enabled' => $prevTaxEnabled, 'tax_rate' => $prevTaxRate],
                'new'      => ['tax_enabled' => $newTaxEnabled, 'tax_rate' => $newTaxRate],
                'ip'       => $request->ip(),
            ],
            $user?->id
        );

        return redirect()->route('settings.tax')
            ->with('success', 'Tax settings updated successfully.');
    }

    /**
     * Read-only JSON endpoint for frontend to display current store tax configuration.
     */
    public function apiGet()
    {
        return response()->json($this->taxService->getSettings());
    }
}

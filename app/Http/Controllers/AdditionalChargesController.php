<?php

namespace App\Http\Controllers;

use App\Models\AdditionalCharge;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdditionalChargesController extends Controller
{
    public function index()
    {
        $charges = AdditionalCharge::latest()->get();
        return view('settings.additional_charges', compact('charges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:fixed,percentage',
            'value'      => 'required|numeric|min:0',
            'is_enabled' => 'nullable|boolean',
        ]);

        $charge = AdditionalCharge::create([
            'name'       => $validated['name'],
            'type'       => $validated['type'],
            'value'      => round((float) $validated['value'], 2),
            'is_enabled' => $request->has('is_enabled') ? (bool) $request->is_enabled : true,
        ]);

        AuditLog::record(
            'additional_charge.created',
            "Additional charge category '{$charge->name}' created",
            'AdditionalCharge',
            $charge->id,
            $charge->toArray(),
            Auth::id()
        );

        return redirect()->route('settings.additional-charges')
            ->with('success', "Charge category '{$charge->name}' created successfully.");
    }

    public function update(Request $request, $id)
    {
        $charge = AdditionalCharge::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:fixed,percentage',
            'value'      => 'required|numeric|min:0',
            'is_enabled' => 'nullable|boolean',
        ]);

        $charge->update([
            'name'       => $validated['name'],
            'type'       => $validated['type'],
            'value'      => round((float) $validated['value'], 2),
            'is_enabled' => $request->has('is_enabled') ? (bool) $request->is_enabled : false,
        ]);

        AuditLog::record(
            'additional_charge.updated',
            "Additional charge category '{$charge->name}' updated",
            'AdditionalCharge',
            $charge->id,
            $charge->toArray(),
            Auth::id()
        );

        return redirect()->route('settings.additional-charges')
            ->with('success', "Charge category '{$charge->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $charge = AdditionalCharge::findOrFail($id);
        $name = $charge->name;
        $charge->delete();

        AuditLog::record(
            'additional_charge.deleted',
            "Additional charge category '{$name}' deleted",
            'AdditionalCharge',
            $id,
            ['name' => $name],
            Auth::id()
        );

        return redirect()->route('settings.additional-charges')
            ->with('success', "Charge category '{$name}' deleted successfully.");
    }

    /**
     * API endpoint returning enabled additional charges for sales/POS page.
     */
    public function apiGet()
    {
        $charges = AdditionalCharge::where('is_enabled', true)->get();
        return response()->json($charges);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;

class SettingsController extends Controller
{
    public function update(Request $request)
    {
        // 1. Validate the specific fields from the legacy app
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'fbr_post_id'   => 'nullable|string',
            'receipt_width' => 'numeric|min:200|max:400',
            'logo'          => 'nullable|image|max:2048',
        ]);

        // 2. Fetch or Create the single record
        $settings = CompanySetting::firstOrNew(['id' => 1]);

        // 3. Handle File Upload (Modernized)
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company', 'public');
            $settings->logo_path = $path;
        }

        // 4. Mass assignment of standard fields
        $settings->fill($request->except('logo'));

        // 5. Explicitly handle checkboxes (standard web form issue)
        $settings->outlook_integration = $request->has('outlook_integration');

        $settings->save();

        return back()->with('success', 'System settings updated successfully.');
    }
}

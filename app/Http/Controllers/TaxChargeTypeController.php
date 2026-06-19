<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaxChargeType;
use Illuminate\Support\Facades\Validator;

class TaxChargeTypeController extends Controller
{
    /**
     * Store a newly created charge / tax type.
     *
     * Called via AJAX POST /tax-charge-types from the Purchase Entry form.
     *
     * Returns:
     *   { success: true,  id: int, name: string }   on success
     *   { success: false, message: string }          on validation failure
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:tax_charge_types,name',
        ], [
            'name.required' => 'Charge type name is required.',
            'name.max'      => 'Name must not exceed 100 characters.',
            'name.unique'   => 'A charge type with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('name'),
            ], 422);
        }

        $chargeType = TaxChargeType::create([
            'name'      => trim($request->name),
            'is_custom' => true,   // user-created types are flagged as custom
        ]);

        return response()->json([
            'success' => true,
            'id'      => $chargeType->id,
            'name'    => $chargeType->name,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcodes.index');
    }

    public function print(Request $request)
    {
        $items = $request->input('items', []);
        $settings = [
            'type' => $request->input('barcode_type', 'C128'),
            'per_row' => $request->input('labels_per_row', 2),
            'gap' => $request->input('gap', 2),
            'show_price' => $request->has('show_price'),
            'show_expiry' => $request->has('show_expiry'),
        ];

        // We return a simple print-friendly view that automatically triggers window.print()
        return view('barcodes.print_template', compact('items', 'settings'));
    }
}

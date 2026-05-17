<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        // Placeholder for listing items
        $items = Item::latest()->paginate(10);
        return view('items.index', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'code' => 'nullable|string|unique:items,code|max:255',
            'cost_price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
        ]);

        $barcode = $request->code;

        // 1. If Code is empty, generate a unique random one
        if (empty($barcode)) {
            do {
                $barcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
            } while (Item::where('code', $barcode)->exists());
        }

        // 2. Generate Barcode Image
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        try {
            $barcodeImage = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);

            // Save Image to Storage (public/barcodes/84920192.svg)
            $imageName = 'barcodes/' . $barcode . '.svg';
            Storage::disk('public')->put($imageName, $barcodeImage);
        } catch (\Exception $e) {
            // Fallback: If image generation fails, proceed but log error (or just don't save path)
            $imageName = null;
        }

        // 3. Prepare Data
        $data = $request->except(['photo', 'cost_price', 'sale_price', 'wholesale_price', 'code']);

        $data['code'] = $barcode;
        $data['barcode_image_path'] = $imageName; // Save path
        $data['cost_rate'] = $request->input('cost_price', 0);
        $data['sale_rate'] = $request->input('sale_price', 0);
        $data['sale_whole'] = $request->input('wholesale_price', 0);

        // Handle Booleans
        $data['hide_sale_price'] = $request->has('hide_sale_price');
        $data['open_price'] = $request->has('open_price');

        // Handle Image Upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('uploads/items', 'public');
            $data['image_path'] = $path;
        }

        $item = Item::create($data);

        return redirect('/items')->with('success', "Item Created! Barcode: {$item->code}");
    }
    public function edit($id)
    {
        // FIFO Logic: Fetch Master Data + Active Batches
        $item = Item::with('activeBatches')->findOrFail($id);
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        // ⚠️ Master Data Update ONLY. Stock/Price are handled via Batches.
        $item = Item::findOrFail($id);

        $request->validate([
            'description' => 'required|string|max:255',
            'code' => 'required|unique:items,code,' . $id,
            'department_id' => 'nullable|exists:departments,id', // Assuming departments table exists, check migration if needed
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'description' => $request->description,
            'code' => $request->code,
            'department_id' => $request->department_id,
            'item_type' => $request->item_type,
            'hide_sale_price' => $request->has('hide_sale_price'),
            'open_price' => $request->has('open_price'),
            // Add other master data fields as needed, but NO STOCK/PRICE
        ];

        // Handle Image Upload
        if ($request->hasFile('photo')) {
            // Delete old image if exists? Optional.
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $request->file('photo')->store('uploads/items', 'public');
        }

        $item->update($data);

        return redirect('/items')->with('success', 'Product Master Details Updated!');
    }
}

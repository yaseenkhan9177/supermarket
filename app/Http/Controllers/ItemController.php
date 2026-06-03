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

    public function import(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx',
        ]);

        $file = $request->file('excel_file');
        
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            
            if ($highestRow <= 1) {
                return response()->json(['message' => 'The uploaded file does not contain any data rows.'], 422);
            }
            
            $rows = $worksheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, false, false);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to parse Excel file: ' . $e->getMessage()], 422);
        }

        // Headers mapping
        $headers = array_map(function($h) {
            return strtolower(trim($h));
        }, $rows[0]);

        $findHeader = function(array $options) use ($headers) {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return false;
        };

        $map = [
            'name' => $findHeader(['name', 'description', 'item name', 'item_name', 'title']),
            'bar_code' => $findHeader(['bar_code', 'barcode', 'code', 'bar code']),
            'type' => $findHeader(['type', 'item_type', 'item type']),
            'packing' => $findHeader(['packing', 'category', 'department', 'dept']),
            'cost' => $findHeader(['cost', 'cost_price', 'cost price', 'cost_rate', 'cost rate']),
            'sale' => $findHeader(['sale', 'sale_price', 'sale price', 'sale_rate', 'sale rate', 'price']),
            'trade' => $findHeader(['trade', 'trade_rate', 'trade rate', 'trade_price', 'trade price']),
            'h_price' => $findHeader(['h_price', 'wholesale_price', 'wholesale price', 'wholesale', 'h price']),
            'stock' => $findHeader(['stock', 'quantity', 'qty', 'opening stock', 'opening_stock', 'on_hand', 'on hand']),
            'min' => $findHeader(['min', 'min_stock', 'min stock', 'minimum']),
            'max' => $findHeader(['max', 'max_stock', 'max stock', 'maximum']),
            'disc' => $findHeader(['disc', 'discount', 'discount_percent', 'discount percent']),
            'openprice' => $findHeader(['openprice', 'open_price', 'open price']),
            'taxrate' => $findHeader(['taxrate', 'taxprate', 'tax_rate', 'tax rate', 'tax']),
            'itemid' => $findHeader(['itemid', 'item_id', 'imported_id', 'imported id', 'id']),
        ];

        // Ensure at least "name" column is found
        if ($map['name'] === false) {
            return response()->json(['message' => 'Required column "name" not found in the Excel sheet.'], 422);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = [];
        $generatedCodesThisImport = [];

        // Cache departments in memory to avoid query per row
        $departments = \App\Models\Department::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();

        // Cache existing item IDs by imported_id and code
        $existingImportedIds = \App\Models\Item::whereNotNull('imported_id')->pluck('id', 'imported_id')->toArray();
        $existingCodes = \App\Models\Item::whereNotNull('code')->pluck('id', 'code')->toArray();

        // DB Transaction wrapping the entire operation
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNumber = $i + 1;

                // 1. Skip rows where name is empty
                $name = isset($row[$map['name']]) ? trim($row[$map['name']]) : '';
                if ($name === '') {
                    continue;
                }

                // 2. Validate cost and sale are numeric
                $costVal = ($map['cost'] !== false && isset($row[$map['cost']]) && $row[$map['cost']] !== '') ? $row[$map['cost']] : null;
                $saleVal = ($map['sale'] !== false && isset($row[$map['sale']]) && $row[$map['sale']] !== '') ? $row[$map['sale']] : null;

                if ($costVal !== null && !is_numeric($costVal)) {
                    $msg = "Row {$rowNumber}: Cost price '{$costVal}' is not numeric.";
                    $skipped[] = $msg;
                    \Illuminate\Support\Facades\Log::warning($msg);
                    continue;
                }
                if ($saleVal !== null && !is_numeric($saleVal)) {
                    $msg = "Row {$rowNumber}: Sale price '{$saleVal}' is not numeric.";
                    $skipped[] = $msg;
                    \Illuminate\Support\Facades\Log::warning($msg);
                    continue;
                }

                // Parse other fields
                $itemid = ($map['itemid'] !== false && isset($row[$map['itemid']]) && $row[$map['itemid']] !== '') ? trim($row[$map['itemid']]) : null;
                $barcode = ($map['bar_code'] !== false && isset($row[$map['bar_code']]) && $row[$map['bar_code']] !== '') ? trim($row[$map['bar_code']]) : null;

                $typeVal = ($map['type'] !== false && isset($row[$map['type']]) && $row[$map['type']] !== '') ? trim($row[$map['type']]) : 'Inventory';
                // Normalize type to match DB constraints
                if (stripos($typeVal, 'service') !== false) {
                    $itemType = 'Service';
                } elseif (stripos($typeVal, 'package') !== false || stripos($typeVal, 'deal') !== false) {
                    $itemType = 'Package';
                } else {
                    $itemType = 'Inventory';
                }

                // Resolve department / category
                $deptId = null;
                $packingVal = ($map['packing'] !== false && isset($row[$map['packing']]) && $row[$map['packing']] !== '') ? trim($row[$map['packing']]) : null;
                if (!empty($packingVal)) {
                    $packingKey = strtolower($packingVal);
                    if (isset($departments[$packingKey])) {
                        $deptId = $departments[$packingKey];
                    } else {
                        $dept = \App\Models\Department::create(['name' => $packingVal]);
                        $departments[$packingKey] = $dept->id;
                        $deptId = $dept->id;
                    }
                }

                // Other numeric inputs
                $tradeRate = ($map['trade'] !== false && isset($row[$map['trade']]) && is_numeric($row[$map['trade']])) ? floatval($row[$map['trade']]) : 0;
                $wholesalePrice = ($map['h_price'] !== false && isset($row[$map['h_price']]) && is_numeric($row[$map['h_price']])) ? floatval($row[$map['h_price']]) : 0;
                $minStock = ($map['min'] !== false && isset($row[$map['min']]) && is_numeric($row[$map['min']])) ? intval($row[$map['min']]) : 0;
                $maxStock = ($map['max'] !== false && isset($row[$map['max']]) && is_numeric($row[$map['max']])) ? intval($row[$map['max']]) : 0;
                $discountPercent = ($map['disc'] !== false && isset($row[$map['disc']]) && is_numeric($row[$map['disc']])) ? floatval($row[$map['disc']]) : 0;
                $taxRate = ($map['taxrate'] !== false && isset($row[$map['taxrate']]) && is_numeric($row[$map['taxrate']])) ? floatval($row[$map['taxrate']]) : 0;

                // Handle openprice flag (boolean)
                $openPriceVal = ($map['openprice'] !== false && isset($row[$map['openprice']]) && $row[$map['openprice']] !== '') ? $row[$map['openprice']] : false;
                $openPrice = false;
                if ($openPriceVal !== false) {
                    $openPrice = filter_var($openPriceVal, FILTER_VALIDATE_BOOLEAN) || in_array(strtolower(strval($openPriceVal)), ['1', 'true', 'yes', 'y']);
                }

                // Find existing item using cached array mapping to avoid DB queries
                $item = null;
                $itemId = null;
                if ($itemid !== null && isset($existingImportedIds[$itemid])) {
                    $itemId = $existingImportedIds[$itemid];
                } elseif (!empty($barcode) && isset($existingCodes[$barcode])) {
                    $itemId = $existingCodes[$barcode];
                }

                if ($itemId) {
                    $item = Item::find($itemId);
                }

                // Handle barcode conflict if item is updated
                if ($item && !empty($barcode) && $item->code !== $barcode) {
                    if (isset($existingCodes[$barcode]) && $existingCodes[$barcode] !== $item->id) {
                        $msg = "Row {$rowNumber}: Barcode '{$barcode}' is already in use by another item (ID: {$existingCodes[$barcode]}).";
                        $skipped[] = $msg;
                        \Illuminate\Support\Facades\Log::warning($msg);
                        continue;
                    }
                }

                $isUpdate = ($item !== null);

                // If not found and code is empty, generate unique barcode
                if (!$item && empty($barcode)) {
                    do {
                        $barcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
                    } while (isset($existingCodes[$barcode]) || in_array($barcode, $generatedCodesThisImport));
                    $generatedCodesThisImport[] = $barcode;
                }

                // If it is a new item and barcode is already in use, skip/conflict check (just in case)
                if (!$item && !empty($barcode)) {
                    if (isset($existingCodes[$barcode])) {
                        $itemId = $existingCodes[$barcode];
                        $item = Item::find($itemId);
                        $isUpdate = true;
                    }
                }

                // Prepare data array
                $data = [
                    'description' => $name,
                    'item_type' => $itemType,
                    'department_id' => $deptId,
                    'cost_rate' => $costVal !== null ? floatval($costVal) : ($isUpdate ? $item->cost_rate : 0),
                    'sale_rate' => $saleVal !== null ? floatval($saleVal) : ($isUpdate ? $item->sale_rate : 0),
                    'trade_rate' => $tradeRate,
                    'sale_whole' => $wholesalePrice,
                    'min_stock' => $minStock,
                    'max_stock' => $maxStock,
                    'discount_percent' => $discountPercent,
                    'open_price' => $openPrice,
                    'tax_rate' => $taxRate,
                ];

                if (!empty($barcode)) {
                    $data['code'] = $barcode;
                }
                if ($itemid !== null) {
                    $data['imported_id'] = $itemid;
                }

                if ($isUpdate) {
                    $item->update($data);
                    $updated++;
                } else {
                    $item = Item::create($data);
                    $inserted++;

                    // Cache newly created mappings
                    if ($item->imported_id !== null) {
                        $existingImportedIds[$item->imported_id] = $item->id;
                    }
                    if ($item->code !== null) {
                        $existingCodes[$item->code] = $item->id;
                    }
                }

                // Generate/Update SVG Barcode image (skip if path is already set)
                $barcodeVal = $item->code;
                if (!empty($barcodeVal)) {
                    $imagePath = $item->barcode_image_path;
                    if (empty($imagePath)) {
                        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                        try {
                            $barcodeImage = $generator->getBarcode($barcodeVal, $generator::TYPE_CODE_128);
                            $imageName = 'barcodes/' . $barcodeVal . '.svg';
                            Storage::disk('public')->put($imageName, $barcodeImage);
                            $item->barcode_image_path = $imageName;
                            $item->save();
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to generate barcode image during import for code {$barcodeVal}: " . $e->getMessage());
                        }
                    }
                }

                // Handle Opening Stock (On Hand) via Batch
                $stockVal = ($map['stock'] !== false && isset($row[$map['stock']]) && $row[$map['stock']] !== '') ? $row[$map['stock']] : null;
                if ($stockVal !== null && is_numeric($stockVal)) {
                    $stockQty = floatval($stockVal);
                    $importBatchNo = 'IMPORT-' . $item->id;

                    $batch = \App\Models\Batch::where('item_id', $item->id)
                        ->where('batch_no', $importBatchNo)
                        ->first();

                    if ($batch) {
                        $batch->quantity_available = $stockQty;
                        $batch->cost_price = $item->cost_rate;
                        $batch->sale_price = $item->sale_rate;
                        $batch->save();
                    } elseif ($stockQty > 0) {
                        \App\Models\Batch::create([
                            'item_id' => $item->id,
                            'batch_no' => $importBatchNo,
                            'quantity_available' => $stockQty,
                            'cost_price' => $item->cost_rate,
                            'sale_price' => $item->sale_rate,
                            'received_at' => now(),
                        ]);
                    }

                    // Re-calculate cached on_hand stock
                    $item->on_hand = \App\Models\Batch::where('item_id', $item->id)
                        ->where('quantity_available', '>', 0)
                        ->sum('quantity_available');
                    $item->save();
                }
            }

            \Illuminate\Support\Facades\DB::commit();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Excel Import failed completely: " . $e->getMessage());
            return response()->json(['message' => 'Import failed due to a database error: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ]);
    }
}

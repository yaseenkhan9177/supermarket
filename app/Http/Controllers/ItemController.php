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
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '256M');

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
            return strtolower(trim((string)$h));
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

        // Chunk processing to avoid execution limit/memory exhaustion
        $rowsData = array_slice($rows, 1);
        $chunks = array_chunk($rowsData, 150);

        foreach ($chunks as $chunkIndex => $chunk) {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 150) + $index + 2;

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
                \Illuminate\Support\Facades\Log::error("Excel Import chunk failed: " . $e->getMessage());
                return response()->json(['message' => 'Import failed due to a database error: ' . $e->getMessage()], 500);
            }
        }

        return response()->json([
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ]);
    }

    public function importPreview()
    {
        $categories = \App\Models\Department::orderBy('name')->get();
        $types = ['inventory', 'service', 'package'];
        return view('items.import-preview', compact('categories', 'types'));
    }

    public function uploadPreview(Request $request)
    {
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '256M');

        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx,csv,txt|max:65536',
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
            return response()->json(['message' => 'Failed to parse file: ' . $e->getMessage()], 422);
        }

        // Headers mapping
        $headers = array_map(function($h) {
            return strtolower(trim((string)$h));
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
            'bar_code' => $findHeader(['bar_code', 'barcode', 'code', 'bar code', 'sku/code', 'sku', 'sku_code']),
            'type' => $findHeader(['type', 'item_type', 'item type']),
            'packing' => $findHeader(['packing', 'category', 'department', 'dept']),
            'cost' => $findHeader(['cost', 'cost_price', 'cost price', 'cost_rate', 'cost rate']),
            'sale' => $findHeader(['sale', 'sale_price', 'sale price', 'sale_rate', 'sale rate', 'price']),
            'trade' => $findHeader(['trade', 'trade_rate', 'trade rate', 'trade_price', 'trade price']),
            'h_price' => $findHeader(['h_price', 'wholesale_price', 'wholesale price', 'wholesale', 'h price']),
            'stock' => $findHeader(['stock', 'quantity', 'qty', 'opening stock', 'opening_stock', 'on_hand', 'on hand']),
            'min' => $findHeader(['min', 'min_stock', 'min stock', 'minimum', 'min stock level', 'min_stock_level']),
            'max' => $findHeader(['max', 'max_stock', 'max stock', 'maximum']),
            'disc' => $findHeader(['disc', 'discount', 'discount_percent', 'discount percent']),
            'openprice' => $findHeader(['openprice', 'open_price', 'open price']),
            'taxrate' => $findHeader(['taxrate', 'taxprate', 'tax_rate', 'tax rate', 'tax']),
            'itemid' => $findHeader(['itemid', 'item_id', 'imported_id', 'imported id', 'id']),
            'unit' => $findHeader(['unit', 'uom', 'measure']),
            'description' => $findHeader(['description', 'desc', 'details', 'item description']),
        ];

        if ($map['name'] === false) {
            return response()->json(['message' => 'Required column "Name" or "Description" not found in the sheet.'], 422);
        }

        $previewRows = [];
        $summary = ['ready' => 0, 'warnings' => 0, 'errors' => 0, 'total' => 0];

        // Cache for check
        $existingCodes = \App\Models\Item::whereNotNull('code')->pluck('id', 'code')->mapWithKeys(function ($id, $code) {
            return [strtolower(trim($code)) => $id];
        })->toArray();

        $existingNames = \App\Models\Item::pluck('id', 'description')->mapWithKeys(function ($id, $desc) {
            return [strtolower(trim($desc)) => $id];
        })->toArray();

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1;

            $name = ($map['name'] !== false && isset($row[$map['name']])) ? trim((string)$row[$map['name']]) : '';
            $type = ($map['type'] !== false && isset($row[$map['type']])) ? strtolower(trim((string)$row[$map['type']])) : 'inventory';
            $sku = ($map['bar_code'] !== false && isset($row[$map['bar_code']])) ? trim((string)$row[$map['bar_code']]) : '';
            $category = ($map['packing'] !== false && isset($row[$map['packing']])) ? trim((string)$row[$map['packing']]) : '';
            $unit = ($map['unit'] !== false && isset($row[$map['unit']])) ? trim((string)$row[$map['unit']]) : '';
            $price = ($map['sale'] !== false && isset($row[$map['sale']]) && $row[$map['sale']] !== '') ? $row[$map['sale']] : 0;
            $cost = ($map['cost'] !== false && isset($row[$map['cost']]) && $row[$map['cost']] !== '') ? $row[$map['cost']] : 0;
            $stock = ($map['stock'] !== false && isset($row[$map['stock']]) && $row[$map['stock']] !== '') ? $row[$map['stock']] : 0;
            $minStock = ($map['min'] !== false && isset($row[$map['min']]) && $row[$map['min']] !== '') ? $row[$map['min']] : 0;
            
            $trade = ($map['trade'] !== false && isset($row[$map['trade']]) && $row[$map['trade']] !== '') ? $row[$map['trade']] : 0;
            $wholesale = ($map['h_price'] !== false && isset($row[$map['h_price']]) && $row[$map['h_price']] !== '') ? $row[$map['h_price']] : 0;
            $maxStock = ($map['max'] !== false && isset($row[$map['max']]) && $row[$map['max']] !== '') ? $row[$map['max']] : 0;
            $discount = ($map['disc'] !== false && isset($row[$map['disc']]) && $row[$map['disc']] !== '') ? $row[$map['disc']] : 0;
            $openPrice = ($map['openprice'] !== false && isset($row[$map['openprice']]) && $row[$map['openprice']] !== '') ? $row[$map['openprice']] : false;
            $taxRate = ($map['taxrate'] !== false && isset($row[$map['taxrate']]) && $row[$map['taxrate']] !== '') ? $row[$map['taxrate']] : 0;
            $importedId = ($map['itemid'] !== false && isset($row[$map['itemid']]) && $row[$map['itemid']] !== '') ? $row[$map['itemid']] : null;
            $description = ($map['description'] !== false && isset($row[$map['description']])) ? trim((string)$row[$map['description']]) : '';

            $issues = [];
            $status = 'ready';

            // Required validations
            if (empty($name)) {
                $status = 'error';
                $issues[] = 'Item Name is required.';
            }

            $validTypes = ['inventory', 'service', 'package'];
            if (!in_array($type, $validTypes)) {
                $status = 'error';
                $issues[] = "Invalid type '{$type}'. Must be one of: inventory, service, package.";
            }

            // Numeric checks
            if (!is_numeric($price)) {
                $status = 'error';
                $issues[] = 'Price must be numeric.';
            }
            if (!is_numeric($cost)) {
                $status = 'error';
                $issues[] = 'Cost must be numeric.';
            }
            if (!is_numeric($stock)) {
                $status = 'error';
                $issues[] = 'Stock must be numeric.';
            }

            // Warnings
            if ($status !== 'error') {
                if (!empty($sku)) {
                    $skuKey = strtolower($sku);
                    if (isset($existingCodes[$skuKey])) {
                        $status = 'warning';
                        $issues[] = "SKU/Code '{$sku}' already exists (will update existing item).";
                    }
                }
                $nameKey = strtolower($name);
                if (isset($existingNames[$nameKey])) {
                    $status = 'warning';
                    $issues[] = "An item with name '{$name}' already exists (will update if match).";
                }
                if (empty($category)) {
                    $status = 'warning';
                    $issues[] = 'Category is empty.';
                }
            }

            // Update counts
            if ($status === 'error') {
                $summary['errors']++;
            } elseif ($status === 'warning') {
                $summary['warnings']++;
            } else {
                $summary['ready']++;
            }
            $summary['total']++;

            $previewRows[] = [
                'index' => $rowNumber,
                'name' => $name,
                'type' => $type,
                'sku' => $sku,
                'category' => $category,
                'unit' => $unit,
                'price' => floatval($price),
                'cost' => floatval($cost),
                'stock' => floatval($stock),
                'min_stock' => floatval($minStock),
                'trade_rate' => floatval($trade),
                'sale_whole' => floatval($wholesale),
                'max_stock' => floatval($maxStock),
                'discount_percent' => floatval($discount),
                'open_price' => $openPrice,
                'tax_rate' => floatval($taxRate),
                'imported_id' => $importedId,
                'description' => $description,
                'status' => $status,
                'issues' => $issues
            ];
        }

        return response()->json([
            'rows' => $previewRows,
            'summary' => $summary
        ]);
    }

    public function importChunk(Request $request)
    {
        @ini_set('max_execution_time', 120);
        @ini_set('memory_limit', '256M');

        $rows = $request->input('rows', []);
        $chunkIndex = $request->input('chunk_index', 0);

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        // Cache departments
        $departments = \App\Models\Department::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();

        // Cache codes & imported_ids
        $existingCodes = \App\Models\Item::whereNotNull('code')->pluck('id', 'code')->mapWithKeys(function ($id, $code) {
            return [strtolower(trim($code)) => $id];
        })->toArray();

        $existingImportedIds = \App\Models\Item::whereNotNull('imported_id')->pluck('id', 'imported_id')->toArray();

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($rows as $rowData) {
                if ($rowData['status'] === 'error') {
                    $skipped++;
                    continue;
                }

                // Race condition/uniqueness check
                $sku = isset($rowData['sku']) ? trim($rowData['sku']) : '';
                $name = isset($rowData['name']) ? trim($rowData['name']) : '';
                $importedId = isset($rowData['imported_id']) ? trim($rowData['imported_id']) : null;

                $item = null;
                $itemId = null;
                if ($importedId !== null && isset($existingImportedIds[$importedId])) {
                    $itemId = $existingImportedIds[$importedId];
                } elseif (!empty($sku) && isset($existingCodes[strtolower($sku)])) {
                    $itemId = $existingCodes[strtolower($sku)];
                }

                if ($itemId) {
                    $item = Item::find($itemId);
                }

                $isUpdate = ($item !== null);

                // Category (Department) firstOrCreate
                $deptId = null;
                $categoryName = isset($rowData['category']) ? trim($rowData['category']) : '';
                if (!empty($categoryName)) {
                    $categoryKey = strtolower($categoryName);
                    if (isset($departments[$categoryKey])) {
                        $deptId = $departments[$categoryKey];
                    } else {
                        $dept = \App\Models\Department::create(['name' => $categoryName]);
                        $departments[$categoryKey] = $dept->id;
                        $deptId = $dept->id;
                    }
                }

                // If not found and code is empty, generate unique barcode
                if (!$item && empty($sku)) {
                    do {
                        $generatedBarcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
                    } while (isset($existingCodes[$generatedBarcode]));
                    $sku = $generatedBarcode;
                }

                // Normalize type
                $typeVal = isset($rowData['type']) ? strtolower(trim($rowData['type'])) : 'inventory';
                if ($typeVal === 'service') {
                    $itemType = 'Service';
                } elseif ($typeVal === 'package') {
                    $itemType = 'Package';
                } else {
                    $itemType = 'Inventory';
                }

                $data = [
                    'description' => $name,
                    'item_type' => $itemType,
                    'department_id' => $deptId,
                    'cost_rate' => isset($rowData['cost']) ? floatval($rowData['cost']) : ($isUpdate ? $item->cost_rate : 0),
                    'sale_rate' => isset($rowData['price']) ? floatval($rowData['price']) : ($isUpdate ? $item->sale_rate : 0),
                    'trade_rate' => isset($rowData['trade_rate']) ? floatval($rowData['trade_rate']) : 0,
                    'sale_whole' => isset($rowData['sale_whole']) ? floatval($rowData['sale_whole']) : 0,
                    'min_stock' => isset($rowData['min_stock']) ? intval($rowData['min_stock']) : 0,
                    'max_stock' => isset($rowData['max_stock']) ? intval($rowData['max_stock']) : 0,
                    'discount_percent' => isset($rowData['discount_percent']) ? floatval($rowData['discount_percent']) : 0,
                    'open_price' => isset($rowData['open_price']) ? filter_var($rowData['open_price'], FILTER_VALIDATE_BOOLEAN) : false,
                    'tax_rate' => isset($rowData['tax_rate']) ? floatval($rowData['tax_rate']) : 0,
                ];

                if (!empty($sku)) {
                    $data['code'] = $sku;
                }
                if ($importedId !== null) {
                    $data['imported_id'] = $importedId;
                }

                // Handle update/create
                if ($isUpdate) {
                    $item->update($data);
                } else {
                    $item = Item::create($data);
                    
                    // Cache the new item mapping
                    if ($item->imported_id !== null) {
                        $existingImportedIds[$item->imported_id] = $item->id;
                    }
                    if ($item->code !== null) {
                        $existingCodes[strtolower($item->code)] = $item->id;
                    }
                }

                // Barcode generation
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
                        } catch (\Exception $ex) {
                            \Illuminate\Support\Facades\Log::error("Failed to generate barcode image: " . $ex->getMessage());
                        }
                    }
                }

                // Batches / stock update
                $stockQty = isset($rowData['stock']) ? floatval($rowData['stock']) : 0;
                if ($stockQty > 0) {
                    $importBatchNo = 'IMPORT-' . $item->id;
                    $batch = \App\Models\Batch::where('item_id', $item->id)
                        ->where('batch_no', $importBatchNo)
                        ->first();

                    if ($batch) {
                        $batch->quantity_available = $stockQty;
                        $batch->cost_price = $item->cost_rate;
                        $batch->sale_price = $item->sale_rate;
                        $batch->save();
                    } else {
                        \App\Models\Batch::create([
                            'item_id' => $item->id,
                            'batch_no' => $importBatchNo,
                            'quantity_available' => $stockQty,
                            'cost_price' => $item->cost_rate,
                            'sale_price' => $item->sale_rate,
                            'received_at' => now(),
                        ]);
                    }

                    // Re-calculate cached stock
                    $item->on_hand = \App\Models\Batch::where('item_id', $item->id)
                        ->where('quantity_available', '>', 0)
                        ->sum('quantity_available');
                    $item->save();
                }

                $imported++;
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("importChunk failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'chunk_index' => $chunkIndex
            ], 500);
        }

        return response()->json([
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'chunk_index' => $chunkIndex
        ]);
    }

    public function downloadSample()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Items
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Items');

        $headers = [
            'Name*',
            'Type*',
            'SKU/Code',
            'Barcode',
            'Category',
            'Unit',
            'Sale Price',
            'Cost Price',
            'Opening Stock',
            'Min Stock Level',
            'Description'
        ];

        foreach ($headers as $colIdx => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        $examples = [
            ['Sugar 1kg', 'inventory', 'SKU001', '123456789', 'Grocery', 'KG', '150', '120', '100', '20', 'White refined sugar'],
            ['Delivery Service', 'service', 'SRV001', '', 'Services', '', '500', '0', '0', '0', 'Home delivery'],
            ['Family Bundle', 'package', 'PKG001', '', 'Packages', 'PCS', '999', '800', '50', '5', 'Bundle deal']
        ];

        foreach ($examples as $rowIdx => $row) {
            foreach ($row as $colIdx => $value) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . ($rowIdx + 2);
                $sheet->setCellValue($cell, $value);
            }
        }

        // Style header row
        $headerRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F7942');

        foreach (range(1, count($headers)) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Sheet 2: Instructions
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Instructions');

        $instructionsHeaders = ['Column', 'Required', 'Description / Valid Values'];
        foreach ($instructionsHeaders as $colIdx => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . '1';
            $sheet2->setCellValue($cell, $header);
        }

        $instructions = [
            ['Name*', 'Yes', 'The name or description of the product.'],
            ['Type*', 'Yes', 'Must be one of: inventory, service, package (case-insensitive).'],
            ['SKU/Code', 'No', 'Unique item code. If already exists, the item details will be updated.'],
            ['Barcode', 'No', 'Optional barcode number.'],
            ['Category', 'No', 'The product category. If the category does not exist, it will be automatically created.'],
            ['Unit', 'No', 'Unit of measurement (e.g., KG, Ltr, PCS). Note: Unit is not currently saved in the database but is included for reference.'],
            ['Sale Price', 'No', 'The main sale price. Defaults to 0 if empty.'],
            ['Cost Price', 'No', 'The item cost price. Defaults to 0 if empty.'],
            ['Opening Stock', 'No', 'The starting stock quantity. Defaults to 0 if empty.'],
            ['Min Stock Level', 'No', 'The threshold to trigger low-stock alerts. Defaults to 0 if empty.'],
            ['Description', 'No', 'Long text details about the item.']
        ];

        foreach ($instructions as $rowIdx => $row) {
            foreach ($row as $colIdx => $value) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . ($rowIdx + 2);
                $sheet2->setCellValue($cell, $value);
            }
        }

        $sheet2HeaderRange = 'A1:C1';
        $sheet2->getStyle($sheet2HeaderRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet2->getStyle($sheet2HeaderRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F7942');

        foreach (range(1, 3) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet2->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Output Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="items_import_template.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}

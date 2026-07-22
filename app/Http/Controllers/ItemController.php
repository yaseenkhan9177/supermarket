<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $items = Item::when($search, fn($q) => $q
                ->where('description', 'LIKE', "%{$search}%")
                ->orWhere('code', 'LIKE', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('items.index', compact('items', 'search'));
    }

    public function show($id)
    {
        $item = Item::with('department')->findOrFail($id);

        // Fetch Purchase History (Batches) with Suppliers via purchase_items -> purchases -> suppliers
        $batches = \App\Models\Batch::where('batches.item_id', $id)
            ->leftJoin('purchase_items', function($join) {
                $join->on('batches.batch_no', '=', 'purchase_items.batch_no')
                     ->on('batches.item_id', '=', 'purchase_items.item_id');
            })
            ->leftJoin('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'batches.*', 
                'suppliers.name as supplier_name'
            )
            ->orderBy('batches.received_at', 'desc')
            ->get();

        // Calculate average cost (weighted) for active stock
        $activeBatches = $batches->where('quantity_available', '>', 0);
        $totalActiveQty = $activeBatches->sum('quantity_available');
        $totalActiveValue = $activeBatches->sum(function ($batch) {
            return $batch->quantity_available * $batch->cost_price;
        });
        
        $averageCost = $totalActiveQty > 0 ? $totalActiveValue / $totalActiveQty : $item->cost_rate;

        // Fetch Sales History
        $salesHistory = \App\Models\SaleItem::with(['sale', 'batch'])
            ->where('sale_items.item_id', $id)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('sale_items.*', 'sales.sale_date as sale_date', 'sales.invoice_no')
            ->orderBy('sales.sale_date', 'desc')
            ->orderBy('sales.id', 'desc')
            ->get();

        // Calculate totals
        $totalQtySold = $salesHistory->sum('qty');
        $totalRevenue = $salesHistory->sum('total');
        
        $totalProfit = $salesHistory->sum(function ($saleItem) use ($item) {
            $cost = $saleItem->batch ? $saleItem->batch->cost_price : $item->cost_rate;
            return $saleItem->total - ($saleItem->qty * $cost);
        });

        return view('items.show', compact(
            'item', 
            'batches', 
            'salesHistory', 
            'averageCost', 
            'totalQtySold', 
            'totalRevenue', 
            'totalProfit'
        ));
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

        if (empty($barcode)) {
            do {
                $barcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
            } while (Item::where('code', $barcode)->exists());
        }

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        try {
            $barcodeImage = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);
            $imageName = 'barcodes/' . $barcode . '.svg';
            Storage::disk('public')->put($imageName, $barcodeImage);
        } catch (\Exception $e) {
            $imageName = null;
        }

        $data = $request->except(['photo', 'cost_price', 'sale_price', 'wholesale_price', 'code']);
        $data['code'] = $barcode;
        $data['barcode_image_path'] = $imageName;
        $data['cost_rate'] = $request->input('cost_price', 0);
        $data['sale_rate'] = $request->input('sale_price', 0);
        $data['sale_whole'] = $request->input('wholesale_price', 0);
        $data['hide_sale_price'] = $request->has('hide_sale_price');
        $data['open_price'] = $request->has('open_price');

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('uploads/items', 'public');
            $data['image_path'] = $path;
        }

        $item = Item::create($data);
        return redirect('/items')->with('success', "Item Created! Barcode: {$item->code}");
    }

    public function edit($id)
    {
        $item = Item::with('activeBatches')->findOrFail($id);
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'description' => 'required|string|max:255',
            'code' => 'required|unique:items,code,' . $id,
            'department_id' => 'nullable|exists:departments,id',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'description' => $request->description,
            'code' => $request->code,
            'department_id' => $request->department_id,
            'item_type' => $request->item_type,
            'hide_sale_price' => $request->has('hide_sale_price'),
            'open_price' => $request->has('open_price'),
        ];

        if ($request->hasFile('photo')) {
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

        $headers = array_map(function ($h) {
            return strtolower(trim((string)$h));
        }, $rows[0]);

        $findHeader = function (array $options) use ($headers) {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) return $idx;
            }
            return false;
        };

        $map = [
            'name'          => $findHeader(['name', 'description', 'item name', 'item_name', 'title']),
            'bar_code'      => $findHeader(['bar_code', 'barcode', 'code', 'bar code']),
            'type'          => $findHeader(['type', 'item_type', 'item type']),
            'packing'       => $findHeader(['packing', 'category', 'department', 'dept']),
            'cost'          => $findHeader(['cost', 'cost_price', 'cost price', 'cost_rate', 'cost rate']),
            'sale'          => $findHeader(['sale', 'sale_price', 'sale price', 'sale_rate', 'sale rate', 'price']),
            'trade'         => $findHeader(['trade', 'trade_rate', 'trade rate', 'trade_price', 'trade price']),
            'h_price'       => $findHeader(['h_price', 'wholesale_price', 'wholesale price', 'wholesale', 'h price']),
            'stock'         => $findHeader(['stock', 'quantity', 'qty', 'opening stock', 'opening_stock', 'on_hand', 'on hand']),
            'min'           => $findHeader(['min', 'min_stock', 'min stock', 'minimum']),
            'max'           => $findHeader(['max', 'max_stock', 'max stock', 'maximum']),
            'disc'          => $findHeader(['disc', 'discount', 'discount_percent', 'discount percent']),
            'openprice'     => $findHeader(['openprice', 'open_price', 'open price']),
            'taxrate'       => $findHeader(['taxrate', 'taxprate', 'tax_rate', 'tax rate', 'tax']),
            'itemid'        => $findHeader(['itemid', 'item_id', 'imported_id', 'imported id', 'id']),
            'supplier'      => $findHeader(['supplier', 'supplier_name', 'supplier name', 'vendor', 'vendor_name', 'vendor name']),
            'supplier_code' => $findHeader(['supplier_code', 'supplier code', 'vendor_code', 'vendor code', 'supplier_id', 'supplier id']),
            'due'           => $findHeader(['due', 'due_amount', 'due amount', 'balance', 'supplier_due', 'supplier due', 'payable']),
        ];

        if ($map['name'] === false) {
            return response()->json(['message' => 'Required column "name" not found in the Excel sheet.'], 422);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = [];
        $generatedCodesThisImport = [];

        $departments = \App\Models\Department::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();

        $existingImportedIds = \App\Models\Item::whereNotNull('imported_id')->pluck('id', 'imported_id')->toArray();
        $existingCodes = \App\Models\Item::whereNotNull('code')->pluck('id', 'code')->toArray();

        $rowsData = array_slice($rows, 1);
        $chunks = array_chunk($rowsData, 150);

        foreach ($chunks as $chunkIndex => $chunk) {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * 150) + $index + 2;
                    $name = isset($row[$map['name']]) ? trim($row[$map['name']]) : '';
                    if ($name === '') continue;

                    $costVal = ($map['cost'] !== false && isset($row[$map['cost']]) && $row[$map['cost']] !== '') ? $row[$map['cost']] : null;
                    $saleVal = ($map['sale'] !== false && isset($row[$map['sale']]) && $row[$map['sale']] !== '') ? $row[$map['sale']] : null;

                    if ($costVal !== null && !is_numeric($costVal)) { $skipped[] = "Row {$rowNumber}: Cost not numeric."; continue; }
                    if ($saleVal !== null && !is_numeric($saleVal)) { $skipped[] = "Row {$rowNumber}: Sale not numeric."; continue; }

                    $itemid  = ($map['itemid'] !== false && isset($row[$map['itemid']]) && $row[$map['itemid']] !== '') ? trim($row[$map['itemid']]) : null;
                    $barcode = ($map['bar_code'] !== false && isset($row[$map['bar_code']]) && $row[$map['bar_code']] !== '') ? trim($row[$map['bar_code']]) : null;

                    $typeVal = ($map['type'] !== false && isset($row[$map['type']]) && $row[$map['type']] !== '') ? trim($row[$map['type']]) : 'Inventory';
                    if (stripos($typeVal, 'service') !== false) $itemType = 'Service';
                    elseif (stripos($typeVal, 'package') !== false || stripos($typeVal, 'deal') !== false) $itemType = 'Package';
                    else $itemType = 'Inventory';

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

                    $tradeRate       = ($map['trade'] !== false && isset($row[$map['trade']]) && is_numeric($row[$map['trade']])) ? floatval($row[$map['trade']]) : 0;
                    $wholesalePrice  = ($map['h_price'] !== false && isset($row[$map['h_price']]) && is_numeric($row[$map['h_price']])) ? floatval($row[$map['h_price']]) : 0;
                    $minStock        = ($map['min'] !== false && isset($row[$map['min']]) && is_numeric($row[$map['min']])) ? intval($row[$map['min']]) : 0;
                    $maxStock        = ($map['max'] !== false && isset($row[$map['max']]) && is_numeric($row[$map['max']])) ? intval($row[$map['max']]) : 0;
                    $discountPercent = ($map['disc'] !== false && isset($row[$map['disc']]) && is_numeric($row[$map['disc']])) ? floatval($row[$map['disc']]) : 0;
                    $taxRate         = ($map['taxrate'] !== false && isset($row[$map['taxrate']]) && is_numeric($row[$map['taxrate']])) ? floatval($row[$map['taxrate']]) : 0;
                    $openPriceVal    = ($map['openprice'] !== false && isset($row[$map['openprice']]) && $row[$map['openprice']] !== '') ? $row[$map['openprice']] : false;
                    $openPrice       = $openPriceVal !== false ? (filter_var($openPriceVal, FILTER_VALIDATE_BOOLEAN) || in_array(strtolower(strval($openPriceVal)), ['1', 'true', 'yes', 'y'])) : false;

                    $item = null; $itemId = null;
                    if ($itemid !== null && isset($existingImportedIds[$itemid])) $itemId = $existingImportedIds[$itemid];
                    elseif (!empty($barcode) && isset($existingCodes[$barcode])) $itemId = $existingCodes[$barcode];
                    if ($itemId) $item = Item::find($itemId);

                    if ($item && !empty($barcode) && $item->code !== $barcode) {
                        if (isset($existingCodes[$barcode]) && $existingCodes[$barcode] !== $item->id) {
                            $skipped[] = "Row {$rowNumber}: Barcode '{$barcode}' in use by another item.";
                            continue;
                        }
                    }

                    $isUpdate = ($item !== null);

                    if (!$item && empty($barcode)) {
                        do { $barcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT); }
                        while (isset($existingCodes[$barcode]) || in_array($barcode, $generatedCodesThisImport));
                        $generatedCodesThisImport[] = $barcode;
                    }

                    if (!$item && !empty($barcode) && isset($existingCodes[$barcode])) {
                        $itemId = $existingCodes[$barcode];
                        $item = Item::find($itemId);
                        $isUpdate = true;
                    }

                    $data = [
                        'description'      => $name,
                        'item_type'        => $itemType,
                        'department_id'    => $deptId,
                        'cost_rate'        => $costVal !== null ? floatval($costVal) : ($isUpdate ? $item->cost_rate : 0),
                        'sale_rate'        => $saleVal !== null ? floatval($saleVal) : ($isUpdate ? $item->sale_rate : 0),
                        'trade_rate'       => $tradeRate,
                        'sale_whole'       => $wholesalePrice,
                        'min_stock'        => $minStock,
                        'max_stock'        => $maxStock,
                        'discount_percent' => $discountPercent,
                        'open_price'       => $openPrice,
                        'tax_rate'         => $taxRate,
                    ];
                    if (!empty($barcode)) $data['code'] = $barcode;
                    if ($itemid !== null) $data['imported_id'] = $itemid;

                    if ($isUpdate) { $item->update($data); $updated++; }
                    else {
                        $item = Item::create($data);
                        $inserted++;
                        if ($item->imported_id !== null) $existingImportedIds[$item->imported_id] = $item->id;
                        if ($item->code !== null) $existingCodes[$item->code] = $item->id;
                    }

                    if (!empty($item->code) && empty($item->barcode_image_path)) {
                        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                        try {
                            $barcodeImage = $generator->getBarcode($item->code, $generator::TYPE_CODE_128);
                            $imageName = 'barcodes/' . $item->code . '.svg';
                            Storage::disk('public')->put($imageName, $barcodeImage);
                            $item->barcode_image_path = $imageName;
                            $item->save();
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Barcode gen failed: " . $e->getMessage());
                        }
                    }

                    $stockVal = ($map['stock'] !== false && isset($row[$map['stock']]) && $row[$map['stock']] !== '') ? $row[$map['stock']] : null;
                    if ($stockVal !== null && is_numeric($stockVal)) {
                        $stockQty = floatval($stockVal);
                        $importBatchNo = 'IMPORT-' . $item->id;
                        $batch = \App\Models\Batch::where('item_id', $item->id)->where('batch_no', $importBatchNo)->first();
                        if ($batch) {
                            $batch->quantity_available = $stockQty;
                            $batch->cost_price = $item->cost_rate;
                            $batch->sale_price = $item->sale_rate;
                            $batch->save();
                        } elseif ($stockQty > 0) {
                            \App\Models\Batch::create([
                                'item_id' => $item->id, 'batch_no' => $importBatchNo,
                                'quantity_available' => $stockQty, 'cost_price' => $item->cost_rate,
                                'sale_price' => $item->sale_rate, 'received_at' => now(),
                            ]);
                        }
                        $item->on_hand = \App\Models\Batch::where('item_id', $item->id)->where('quantity_available', '>', 0)->sum('quantity_available');
                        $item->save();
                    }

                    $supplierName = ($map['supplier'] !== false && isset($row[$map['supplier']])) ? trim($row[$map['supplier']]) : '';
                    $supplierCode = ($map['supplier_code'] !== false && isset($row[$map['supplier_code']])) ? trim($row[$map['supplier_code']]) : '';
                    $dueVal       = ($map['due'] !== false && isset($row[$map['due']]) && $row[$map['due']] !== '') ? $row[$map['due']] : 0;
                    $dueAmount    = is_numeric($dueVal) ? floatval($dueVal) : 0;

                    if ($dueAmount > 0 && ($supplierName !== '' || $supplierCode !== '')) {
                        $stockQtyForNote = isset($stockQty) ? $stockQty : 1;
                        $this->resolveSupplierAndRecordLedger(
                            $supplierName,
                            $supplierCode,
                            $dueAmount,
                            $name,
                            $stockQtyForNote,
                            $item->cost_rate,
                            $rowNumber,
                            $file->getClientOriginalName()
                        );
                    }
                }
                \Illuminate\Support\Facades\DB::commit();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['inserted' => $inserted, 'updated' => $updated, 'skipped_count' => count($skipped), 'skipped' => $skipped]);
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
            'excel_file' => 'required|file|mimes:csv,txt|max:65536',
        ]);

        $file = $request->file('excel_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return response()->json(['message' => 'Could not open file.'], 422);
        }

        $rows = [];
        while (($row = fgetcsv($handle, 10000, ',')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (count($rows) <= 1) {
            return response()->json(['message' => 'File has no data rows.'], 422);
        }

        // Map headers
        $headers = array_map(function($h) { return strtolower(trim((string)$h)); }, $rows[0]);

        $findHeader = function(array $options) use ($headers) {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) return $idx;
            }
            return false;
        };

        $map = [
            'name'          => $findHeader(['name', 'description', 'item name', 'item_name', 'title']),
            'bar_code'      => $findHeader(['bar_code', 'barcode', 'code', 'bar code', 'sku/code', 'sku']),
            'type'          => $findHeader(['type', 'item_type', 'item type']),
            'packing'       => $findHeader(['packing', 'category', 'department', 'dept']),
            'cost'          => $findHeader(['cost', 'cost_price', 'cost price', 'cost_rate']),
            'sale'          => $findHeader(['sale', 'sale_price', 'sale price', 'sale_rate', 'price']),
            'stock'         => $findHeader(['stock', 'quantity', 'qty', 'opening stock', 'opening_stock', 'on_hand']),
            'min'           => $findHeader(['min', 'min_stock', 'min stock', 'minimum', 'min stock level']),
            'unit'          => $findHeader(['unit', 'uom', 'measure']),
            'supplier'      => $findHeader(['supplier', 'supplier_name', 'supplier name', 'vendor', 'vendor_name', 'vendor name']),
            'supplier_code' => $findHeader(['supplier_code', 'supplier code', 'vendor_code', 'vendor code', 'supplier_id', 'supplier id']),
            'due'           => $findHeader(['due', 'due_amount', 'due amount', 'balance', 'supplier_due', 'supplier due', 'payable']),
        ];

        if ($map['name'] === false) {
            return response()->json(['message' => 'Required column "Name" not found. Make sure your CSV has a "Name" column header.'], 422);
        }

        $previewRows = [];
        $summary = ['ready' => 0, 'warnings' => 0, 'errors' => 0, 'total' => 0];

        $existingCodes = \App\Models\Item::whereNotNull('code')->pluck('id', 'code')
            ->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])->toArray();
        $existingNames = \App\Models\Item::pluck('id', 'description')
            ->mapWithKeys(fn($id, $desc) => [strtolower(trim($desc)) => $id])->toArray();

        $suppliersByCode = Supplier::whereNotNull('code')->get()
            ->mapWithKeys(fn($s) => [strtolower(trim($s->code)) => $s])->toArray();
        $suppliersByName = Supplier::get()
            ->mapWithKeys(fn($s) => [strtolower(trim($s->name)) => $s])->toArray();

        $supplierGroups = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip completely empty rows
            if (empty(array_filter($row, fn($v) => trim($v) !== ''))) continue;

            $name         = ($map['name'] !== false && isset($row[$map['name']])) ? trim($row[$map['name']]) : '';
            $type         = ($map['type'] !== false && isset($row[$map['type']])) ? strtolower(trim($row[$map['type']])) : 'inventory';
            $sku          = ($map['bar_code'] !== false && isset($row[$map['bar_code']])) ? trim($row[$map['bar_code']]) : '';
            $category     = ($map['packing'] !== false && isset($row[$map['packing']])) ? trim($row[$map['packing']]) : '';
            $unit         = ($map['unit'] !== false && isset($row[$map['unit']])) ? trim($row[$map['unit']]) : '';
            $price        = ($map['sale'] !== false && isset($row[$map['sale']]) && $row[$map['sale']] !== '') ? $row[$map['sale']] : 0;
            $cost         = ($map['cost'] !== false && isset($row[$map['cost']]) && $row[$map['cost']] !== '') ? $row[$map['cost']] : 0;
            $stock        = ($map['stock'] !== false && isset($row[$map['stock']]) && $row[$map['stock']] !== '') ? $row[$map['stock']] : 0;
            $minStock     = ($map['min'] !== false && isset($row[$map['min']]) && $row[$map['min']] !== '') ? $row[$map['min']] : 0;

            $supplierName = ($map['supplier'] !== false && isset($row[$map['supplier']])) ? trim($row[$map['supplier']]) : '';
            $supplierCode = ($map['supplier_code'] !== false && isset($row[$map['supplier_code']])) ? trim($row[$map['supplier_code']]) : '';
            $dueAmountVal = ($map['due'] !== false && isset($row[$map['due']]) && $row[$map['due']] !== '') ? $row[$map['due']] : 0;

            $issues = []; $status = 'ready';

            if (empty($name)) { $status = 'error'; $issues[] = 'Name is required.'; }
            if (!in_array($type, ['inventory', 'service', 'package'])) { $status = 'error'; $issues[] = "Invalid type '{$type}'. Must be: inventory, service, or package."; }
            if (!is_numeric($price)) { $status = 'error'; $issues[] = 'Sale Price must be numeric.'; }
            if (!is_numeric($cost))  { $status = 'error'; $issues[] = 'Cost Price must be numeric.'; }
            if (!is_numeric($stock)) { $status = 'error'; $issues[] = 'Stock must be numeric.'; }
            if ($dueAmountVal !== '' && !is_numeric($dueAmountVal)) { $status = 'error'; $issues[] = 'Due Amount must be numeric.'; }

            $dueAmount = is_numeric($dueAmountVal) ? floatval($dueAmountVal) : 0;

            if ($status !== 'error') {
                if (!empty($sku) && isset($existingCodes[strtolower($sku)])) { $status = 'warning'; $issues[] = "SKU '{$sku}' already exists — will update."; }
                elseif (!empty($name) && isset($existingNames[strtolower($name)])) { $status = 'warning'; $issues[] = "Name '{$name}' already exists."; }
                if (empty($category)) { $status = 'warning'; $issues[] = 'Category is empty.'; }
            }

            if ($status === 'error') $summary['errors']++;
            elseif ($status === 'warning') $summary['warnings']++;
            else $summary['ready']++;
            $summary['total']++;

            $previewRows[] = [
                'index'         => $i + 1,
                'name'          => $name,
                'type'          => $type,
                'sku'           => $sku,
                'category'      => $category,
                'unit'          => $unit,
                'price'         => floatval($price),
                'cost'          => floatval($cost),
                'stock'         => floatval($stock),
                'min_stock'     => floatval($minStock),
                'supplier_name' => $supplierName,
                'supplier_code' => $supplierCode,
                'due'           => $dueAmount,
                'status'        => $status,
                'issues'        => $issues,
            ];

            if ($status !== 'error' && $dueAmount > 0 && ($supplierName !== '' || $supplierCode !== '')) {
                $groupKey = strtolower($supplierCode !== '' ? $supplierCode : $supplierName);
                if (!isset($supplierGroups[$groupKey])) {
                    $supp = null;
                    if ($supplierCode !== '' && isset($suppliersByCode[strtolower($supplierCode)])) {
                        $supp = $suppliersByCode[strtolower($supplierCode)];
                    } elseif ($supplierName !== '' && isset($suppliersByName[strtolower($supplierName)])) {
                        $supp = $suppliersByName[strtolower($supplierName)];
                    }

                    $supplierGroups[$groupKey] = [
                        'name'             => $supp ? $supp->name : ($supplierName ?: $supplierCode),
                        'code'             => $supp ? $supp->code : ($supplierCode ?: 'Auto-Gen'),
                        'is_new'           => $supp === null,
                        'current_balance'  => $supp ? floatval($supp->current_balance) : 0.0,
                        'due_total'        => 0.0,
                        'rows'             => [],
                    ];
                }
                $supplierGroups[$groupKey]['due_total'] += $dueAmount;
                $supplierGroups[$groupKey]['rows'][] = [
                    'row'  => $i + 1,
                    'item' => $name,
                    'due'  => $dueAmount,
                ];
            }
        }

        $supplierDues = [];
        $totalSupplierDues = 0.0;
        foreach ($supplierGroups as $group) {
            $group['projected_balance'] = $group['current_balance'] + $group['due_total'];
            $supplierDues[] = $group;
            $totalSupplierDues += $group['due_total'];
        }

        return response()->json([
            'rows'                => $previewRows,
            'summary'             => $summary,
            'supplier_dues'       => $supplierDues,
            'total_supplier_dues' => $totalSupplierDues,
        ]);
    }

    public function importChunk(Request $request)
    {
        @ini_set('max_execution_time', 120);
        @ini_set('memory_limit', '256M');

        $rows = $request->input('rows', []);
        $chunkIndex = $request->input('chunk_index', 0);
        $fileName = $request->input('file_name', 'stock_upload.csv');
        $imported = 0; $skipped = 0; $failed = 0;

        $departments = \App\Models\Department::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();

        $existingCodes = \App\Models\Item::whereNotNull('code')->pluck('id', 'code')->mapWithKeys(function ($id, $code) {
            return [strtolower(trim($code)) => $id];
        })->toArray();

        $existingImportedIds = \App\Models\Item::whereNotNull('imported_id')->pluck('id', 'imported_id')->toArray();

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($rows as $rowData) {
                if (isset($rowData['status']) && $rowData['status'] === 'error') { $skipped++; continue; }

                $sku = isset($rowData['sku']) ? trim($rowData['sku']) : '';
                $name = isset($rowData['name']) ? trim($rowData['name']) : '';
                $importedId = isset($rowData['imported_id']) ? trim($rowData['imported_id']) : null;

                $item = null; $itemId = null;
                if ($importedId !== null && isset($existingImportedIds[$importedId])) $itemId = $existingImportedIds[$importedId];
                elseif (!empty($sku) && isset($existingCodes[strtolower($sku)])) $itemId = $existingCodes[strtolower($sku)];
                if ($itemId) $item = Item::find($itemId);

                $isUpdate = ($item !== null);

                $deptId = null;
                $categoryName = isset($rowData['category']) ? trim($rowData['category']) : '';
                if (!empty($categoryName)) {
                    $categoryKey = strtolower($categoryName);
                    if (isset($departments[$categoryKey])) { $deptId = $departments[$categoryKey]; }
                    else { $dept = \App\Models\Department::create(['name' => $categoryName]); $departments[$categoryKey] = $dept->id; $deptId = $dept->id; }
                }

                if (!$item && empty($sku)) {
                    do { $generatedBarcode = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT); }
                    while (isset($existingCodes[$generatedBarcode]));
                    $sku = $generatedBarcode;
                }

                $typeVal = isset($rowData['type']) ? strtolower(trim($rowData['type'])) : 'inventory';
                if ($typeVal === 'service') $itemType = 'Service';
                elseif ($typeVal === 'package') $itemType = 'Package';
                else $itemType = 'Inventory';

                $data = [
                    'description'      => $name,
                    'item_type'        => $itemType,
                    'department_id'    => $deptId,
                    'cost_rate'        => isset($rowData['cost']) ? floatval($rowData['cost']) : ($isUpdate ? $item->cost_rate : 0),
                    'sale_rate'        => isset($rowData['price']) ? floatval($rowData['price']) : ($isUpdate ? $item->sale_rate : 0),
                    'trade_rate'       => isset($rowData['trade_rate']) ? floatval($rowData['trade_rate']) : 0,
                    'sale_whole'       => isset($rowData['sale_whole']) ? floatval($rowData['sale_whole']) : 0,
                    'min_stock'        => isset($rowData['min_stock']) ? intval($rowData['min_stock']) : 0,
                    'max_stock'        => isset($rowData['max_stock']) ? intval($rowData['max_stock']) : 0,
                    'discount_percent' => isset($rowData['discount_percent']) ? floatval($rowData['discount_percent']) : 0,
                    'open_price'       => isset($rowData['open_price']) ? filter_var($rowData['open_price'], FILTER_VALIDATE_BOOLEAN) : false,
                    'tax_rate'         => isset($rowData['tax_rate']) ? floatval($rowData['tax_rate']) : 0,
                ];
                if (!empty($sku)) $data['code'] = $sku;
                if ($importedId !== null) $data['imported_id'] = $importedId;

                if ($isUpdate) { $item->update($data); }
                else {
                    $item = Item::create($data);
                    if ($item->imported_id !== null) $existingImportedIds[$item->imported_id] = $item->id;
                    if ($item->code !== null) $existingCodes[strtolower($item->code)] = $item->id;
                }

                if (!empty($item->code) && empty($item->barcode_image_path)) {
                    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                    try {
                        $barcodeImage = $generator->getBarcode($item->code, $generator::TYPE_CODE_128);
                        $imageName = 'barcodes/' . $item->code . '.svg';
                        Storage::disk('public')->put($imageName, $barcodeImage);
                        $item->barcode_image_path = $imageName;
                        $item->save();
                    } catch (\Exception $ex) {
                        \Illuminate\Support\Facades\Log::error("Barcode gen failed: " . $ex->getMessage());
                    }
                }

                $stockQty = isset($rowData['stock']) ? floatval($rowData['stock']) : 0;
                if ($stockQty > 0) {
                    $importBatchNo = 'IMPORT-' . $item->id;
                    $batch = \App\Models\Batch::where('item_id', $item->id)->where('batch_no', $importBatchNo)->first();
                    if ($batch) {
                        $batch->quantity_available = $stockQty; $batch->cost_price = $item->cost_rate; $batch->sale_price = $item->sale_rate; $batch->save();
                    } else {
                        \App\Models\Batch::create(['item_id' => $item->id, 'batch_no' => $importBatchNo, 'quantity_available' => $stockQty, 'cost_price' => $item->cost_rate, 'sale_price' => $item->sale_rate, 'received_at' => now()]);
                    }
                    $item->on_hand = \App\Models\Batch::where('item_id', $item->id)->where('quantity_available', '>', 0)->sum('quantity_available');
                    $item->save();
                }

                $supplierName = isset($rowData['supplier_name']) ? trim($rowData['supplier_name']) : '';
                $supplierCode = isset($rowData['supplier_code']) ? trim($rowData['supplier_code']) : '';
                $dueAmount    = isset($rowData['due']) ? floatval($rowData['due']) : 0;
                $rowNumber    = isset($rowData['index']) ? intval($rowData['index']) : 0;

                if ($dueAmount > 0 && ($supplierName !== '' || $supplierCode !== '')) {
                    $this->resolveSupplierAndRecordLedger(
                        $supplierName,
                        $supplierCode,
                        $dueAmount,
                        $name,
                        $stockQty,
                        isset($rowData['cost']) ? floatval($rowData['cost']) : ($isUpdate ? $item->cost_rate : 0),
                        $rowNumber,
                        $fileName
                    );
                }

                $imported++;
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("importChunk failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'chunk_index' => $chunkIndex], 500);
        }

        return response()->json(['imported' => $imported, 'skipped' => $skipped, 'failed' => $failed, 'chunk_index' => $chunkIndex]);
    }

    protected function resolveSupplierAndRecordLedger(
        ?string $supplierName,
        ?string $supplierCode,
        float $dueAmount,
        string $itemName,
        float $qty,
        float $costPrice,
        int $rowNumber,
        string $fileName
    ): void {
        if ($dueAmount <= 0) {
            return;
        }

        $supplierName = trim((string)$supplierName);
        $supplierCode = trim((string)$supplierCode);

        if ($supplierName === '' && $supplierCode === '') {
            return;
        }

        $supplier = null;

        // 1. Match by code first (exact case-insensitive)
        if ($supplierCode !== '') {
            $supplier = Supplier::whereRaw('LOWER(code) = ?', [strtolower($supplierCode)])->first();
        }

        // 2. Fallback to exact name match (exact case-insensitive)
        if (!$supplier && $supplierName !== '') {
            $supplier = Supplier::whereRaw('LOWER(name) = ?', [strtolower($supplierName)])->first();
        }

        // 3. Create new supplier if no match exists
        if (!$supplier) {
            $newCode = $supplierCode !== '' ? strtoupper($supplierCode) : ('SUP-' . strtoupper(Str::random(6)));
            $newName = $supplierName !== '' ? $supplierName : $newCode;

            $supplier = Supplier::create([
                'name'            => $newName,
                'code'            => $newCode,
                'current_balance' => 0,
                'opening_balance' => 0,
            ]);
        }

        // Increase payable balance
        $supplier->current_balance += $dueAmount;
        $supplier->save();

        // Create supplier ledger entry with item detail note
        $formattedCost = number_format($costPrice, 2, '.', '');
        $note = "Imported via stock upload - {$fileName} - Row #{$rowNumber} ({$itemName} x {$qty} @ Rs.{$formattedCost})";

        SupplierLedgerEntry::create([
            'supplier_id'   => $supplier->id,
            'type'          => 'purchase',
            'amount'        => $dueAmount,
            'balance_after' => $supplier->current_balance,
            'note'          => $note,
            'created_by'    => auth()->id(),
        ]);
    }

    public function downloadSample()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="items-sample.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $rows = [
            ['Name*', 'Type*', 'SKU/Code', 'Barcode', 'Category', 'Unit', 'Sale Price', 'Cost Price', 'Opening Stock', 'Min Stock Level', 'Supplier Name', 'Supplier Code', 'Due Amount', 'Description'],
            ['Sugar 1kg', 'inventory', 'SKU001', '123456789', 'Grocery', 'KG', '150', '120', '100', '20', 'ABC Distributors', 'SUP-001', '5000', 'White refined sugar'],
            ['Delivery Service', 'service', 'SRV001', '', 'Services', '', '500', '0', '0', '0', '', '', '0', 'Home delivery service'],
            ['Family Bundle', 'package', 'PKG001', '', 'Packages', 'PCS', '999', '800', '50', '5', 'Global Suppliers', 'SUP-002', '12000', 'Bundle of essential items'],
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
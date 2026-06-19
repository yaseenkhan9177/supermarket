<?php

use App\Models\Item;
use App\Models\User;
use App\Models\Batch;
use App\Models\Department;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
});

/**
 * Helper to generate an Excel file at a temporary path
 */
function createTestExcel(array $rows): string
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    foreach ($rows as $rowIndex => $row) {
        foreach ($row as $colIndex => $value) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . ($rowIndex + 1), $value);
        }
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'import_') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFile);

    return $tempFile;
}

it('requires authentication to access the import route', function () {
    $response = $this->post(route('items.import'), [
        'excel_file' => UploadedFile::fake()->create('items.xlsx', 100),
    ]);

    $response->assertRedirect('/login');
});

it('validates the uploaded file is required and of xls/xlsx format', function () {
    $response = $this->actingAs($this->user)->postJson(route('items.import'), []);
    $response->assertStatus(422);

    $response = $this->actingAs($this->user)->postJson(route('items.import'), [
        'excel_file' => UploadedFile::fake()->create('items.txt', 100),
    ]);
    $response->assertStatus(422);
});

it('successfully imports new items with dynamic header mapping', function () {
    $excelPath = createTestExcel([
        ['item name', 'bar code', 'cost price', 'sale price', 'category', 'qty', 'min stock', 'max stock'],
        ['Fresh Milk 1L', '11112222', '120.00', '150.00', 'Dairy', '25', '5', '50'],
        ['White Bread', '', '80.00', '95.00', 'Bakery', '15', '2', '20'],
    ]);

    $response = $this->actingAs($this->user)->post(route('items.import'), [
        'excel_file' => new UploadedFile($excelPath, 'items.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    @unlink($excelPath);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'inserted' => 2,
        'updated' => 0,
        'skipped_count' => 0,
    ]);

    // Check Fresh Milk 1L
    $milk = Item::where('description', 'Fresh Milk 1L')->first();
    expect($milk)->not->toBeNull();
    expect($milk->code)->toBe('11112222');
    expect((float)$milk->cost_rate)->toBe(120.0);
    expect((float)$milk->sale_rate)->toBe(150.0);
    expect($milk->min_stock)->toBe(5);
    expect($milk->max_stock)->toBe(50);
    expect((float)$milk->on_hand)->toBe(25.0);

    // Department resolved
    $deptMilk = Department::where('name', 'Dairy')->first();
    expect($deptMilk)->not->toBeNull();
    expect($milk->department_id)->toBe($deptMilk->id);

    // FIFO Batch generated
    $milkBatch = Batch::where('item_id', $milk->id)->where('batch_no', 'IMPORT-' . $milk->id)->first();
    expect($milkBatch)->not->toBeNull();
    expect((float)$milkBatch->quantity_available)->toBe(25.0);
    expect((float)$milkBatch->cost_price)->toBe(120.0);
    expect((float)$milkBatch->sale_price)->toBe(150.0);

    // Barcode image generated and saved
    expect($milk->barcode_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($milk->barcode_image_path);

    // Check White Bread (barcode should be auto-generated)
    $bread = Item::where('description', 'White Bread')->first();
    expect($bread)->not->toBeNull();
    expect($bread->code)->not->toBeEmpty();
    expect(strlen($bread->code))->toBe(8);
    expect((float)$bread->cost_rate)->toBe(80.0);
    expect((float)$bread->sale_rate)->toBe(95.0);
    expect((float)$bread->on_hand)->toBe(15.0);

    $breadBatch = Batch::where('item_id', $bread->id)->where('batch_no', 'IMPORT-' . $bread->id)->first();
    expect($breadBatch)->not->toBeNull();
    expect((float)$breadBatch->quantity_available)->toBe(15.0);

    expect($bread->barcode_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($bread->barcode_image_path);
});

it('updates existing items instead of duplicating them', function () {
    // Seed an existing item matching by code and one by imported_id
    $existing1 = Item::create([
        'code' => '55556666',
        'description' => 'Old Milk',
        'cost_rate' => 100,
        'sale_rate' => 120,
    ]);

    $existing2 = Item::create([
        'code' => '77778888',
        'description' => 'Old Bread',
        'imported_id' => 'EXT-100',
        'cost_rate' => 70,
        'sale_rate' => 85,
    ]);

    $excelPath = createTestExcel([
        ['id', 'barcode', 'name', 'cost', 'sale', 'stock'],
        ['', '55556666', 'New Milk Name', '110.00', '135.00', '10'], // match by code
        ['EXT-100', '99999999', 'New Bread Name', '75.00', '90.00', '20'], // match by imported_id (and change code)
    ]);

    $response = $this->actingAs($this->user)->post(route('items.import'), [
        'excel_file' => new UploadedFile($excelPath, 'items.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    @unlink($excelPath);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'inserted' => 0,
        'updated' => 2,
        'skipped_count' => 0,
    ]);

    // Verify existing1 is updated
    $existing1->refresh();
    expect($existing1->description)->toBe('New Milk Name');
    expect((float)$existing1->cost_rate)->toBe(110.0);
    expect((float)$existing1->sale_rate)->toBe(135.0);
    expect((float)$existing1->on_hand)->toBe(10.0);

    $batch1 = Batch::where('item_id', $existing1->id)->first();
    expect($batch1)->not->toBeNull();
    expect((float)$batch1->quantity_available)->toBe(10.0);

    // Verify existing2 is updated including barcode
    $existing2->refresh();
    expect($existing2->description)->toBe('New Bread Name');
    expect($existing2->code)->toBe('99999999');
    expect((float)$existing2->cost_rate)->toBe(75.0);
    expect((float)$existing2->sale_rate)->toBe(90.0);
    expect((float)$existing2->on_hand)->toBe(20.0);

    $batch2 = Batch::where('item_id', $existing2->id)->first();
    expect($batch2)->not->toBeNull();
    expect((float)$batch2->quantity_available)->toBe(20.0);
});

it('skips rows with validation errors and logs reasons', function () {
    $excelPath = createTestExcel([
        ['name', 'barcode', 'cost', 'sale'],
        ['Valid Item', '12345678', '50.00', '65.00'],
        ['Invalid Cost Item', '12345679', 'abc', '65.00'],
        ['Invalid Sale Item', '12345680', '50.00', 'xyz'],
        ['', '12345681', '10.00', '15.00'], // missing name, skipped silently
    ]);

    $response = $this->actingAs($this->user)->post(route('items.import'), [
        'excel_file' => new UploadedFile($excelPath, 'items.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    @unlink($excelPath);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'inserted' => 1,
        'updated' => 0,
        'skipped_count' => 2,
    ]);

    $data = $response->json();
    expect($data['skipped'])->toContain("Row 3: Cost price 'abc' is not numeric.");
    expect($data['skipped'])->toContain("Row 4: Sale price 'xyz' is not numeric.");

    // Valid item exists
    expect(Item::where('description', 'Valid Item')->exists())->toBeTrue();
    // Invalid items do not exist
    expect(Item::where('description', 'Invalid Cost Item')->exists())->toBeFalse();
    expect(Item::where('description', 'Invalid Sale Item')->exists())->toBeFalse();
});

it('rolls back the entire transaction if any database/integrity failure occurs during processing', function () {
    // Setup event listener to simulate integrity exception during import
    Item::saving(function ($item) {
        if ($item->description === 'FAIL_ME') {
            throw new \RuntimeException('Simulated Database Exception');
        }
    });

    $excelPath = createTestExcel([
        ['name', 'barcode', 'cost', 'sale'],
        ['Good Item Before', '10001001', '10.00', '15.00'],
        ['FAIL_ME', '10001002', '20.00', '30.00'],
        ['Good Item After', '10001003', '30.00', '45.00'],
    ]);

    $response = $this->actingAs($this->user)->post(route('items.import'), [
        'excel_file' => new UploadedFile($excelPath, 'items.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    @unlink($excelPath);

    $response->assertStatus(500);
    $response->assertJsonFragment([
        'message' => 'Import failed due to a database error: Simulated Database Exception',
    ]);

    // Assert that absolutely nothing was imported (nothing is half-imported)
    expect(Item::where('description', 'Good Item Before')->exists())->toBeFalse();
    expect(Item::where('description', 'FAIL_ME')->exists())->toBeFalse();
    expect(Item::where('description', 'Good Item After')->exists())->toBeFalse();
});

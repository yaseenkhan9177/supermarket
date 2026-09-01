<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Batch;
use App\Models\Department;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ItemImportTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
        try {
            $role = \Spatie\Permission\Models\Role::findOrCreate('manager');
            $this->user->assignRole($role);
        } catch (\Throwable $e) {}
    }

    private function createTestExcel(array $rows): string
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

    public function test_requires_authentication_to_access_the_import_route()
    {
        $response = $this->post(route('items.import'), [
            'excel_file' => UploadedFile::fake()->create('items.xlsx', 100),
        ]);

        $response->assertRedirect('/login');
    }

    public function test_validates_the_uploaded_file_is_required_and_of_xls_xlsx_format()
    {
        $response = $this->actingAs($this->user)->postJson(route('items.import'), []);
        $response->assertStatus(422);

        $response = $this->actingAs($this->user)->postJson(route('items.import'), [
            'excel_file' => UploadedFile::fake()->create('items.txt', 100),
        ]);
        $response->assertStatus(422);
    }

    public function test_successfully_imports_new_items_with_dynamic_header_mapping()
    {
        $excelPath = $this->createTestExcel([
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
        $this->assertNotNull($milk);
        $this->assertEquals('11112222', $milk->code);
        $this->assertEquals(120.0, (float)$milk->cost_rate);
        $this->assertEquals(150.0, (float)$milk->sale_rate);
        $this->assertEquals(5, $milk->min_stock);
        $this->assertEquals(50, $milk->max_stock);
        $this->assertEquals(25.0, (float)$milk->on_hand);

        // Department resolved
        $deptMilk = Department::where('name', 'Dairy')->first();
        $this->assertNotNull($deptMilk);
        $this->assertEquals($deptMilk->id, $milk->department_id);

        // FIFO Batch generated
        $milkBatch = Batch::where('item_id', $milk->id)->where('batch_no', 'IMPORT-' . $milk->id)->first();
        $this->assertNotNull($milkBatch);
        $this->assertEquals(25.0, (float)$milkBatch->quantity_available);

        // Barcode image generated and saved
        $this->assertNotNull($milk->barcode_image_path);
        Storage::disk('public')->assertExists($milk->barcode_image_path);

        // Check White Bread
        $bread = Item::where('description', 'White Bread')->first();
        $this->assertNotNull($bread);
        $this->assertNotEmpty($bread->code);
        $this->assertEquals(8, strlen($bread->code));
        $this->assertEquals(80.0, (float)$bread->cost_rate);
        $this->assertEquals(95.0, (float)$bread->sale_rate);
        $this->assertEquals(15.0, (float)$bread->on_hand);
    }

    public function test_updates_existing_items_instead_of_duplicating_them()
    {
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

        $excelPath = $this->createTestExcel([
            ['id', 'barcode', 'name', 'cost', 'sale', 'stock'],
            ['', '55556666', 'New Milk Name', '110.00', '135.00', '10'],
            ['EXT-100', '99999999', 'New Bread Name', '75.00', '90.00', '20'],
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

        $existing1->refresh();
        $this->assertEquals('New Milk Name', $existing1->description);
        $this->assertEquals(110.0, (float)$existing1->cost_rate);

        $existing2->refresh();
        $this->assertEquals('New Bread Name', $existing2->description);
        $this->assertEquals('99999999', $existing2->code);
    }

    public function test_skips_rows_with_validation_errors_and_logs_reasons()
    {
        $excelPath = $this->createTestExcel([
            ['name', 'barcode', 'cost', 'sale'],
            ['Valid Item', '12345678', '50.00', '65.00'],
            ['Invalid Cost Item', '12345679', 'abc', '65.00'],
            ['Invalid Sale Item', '12345680', '50.00', 'xyz'],
            ['', '12345681', '10.00', '15.00'],
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
        $this->assertCount(2, $data['skipped']);
        $this->assertTrue(Item::where('description', 'Valid Item')->exists());
        $this->assertFalse(Item::where('description', 'Invalid Cost Item')->exists());
        $this->assertFalse(Item::where('description', 'Invalid Sale Item')->exists());
    }
}

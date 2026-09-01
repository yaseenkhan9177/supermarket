<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleVersion;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NewSalesFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Wallet $wallet;
    protected Item $item;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->wallet = Wallet::create([
            'name'       => 'Test Wallet',
            'type'       => 'Cash',
            'balance'    => 1000.00,
            'is_active'  => true,
        ]);
        $this->item = Item::create([
            'code'        => 'TEST-ITEM-001',
            'description' => 'Test Product',
            'sale_rate'   => 100.00,
            'on_hand'     => 50,
            'item_type'   => 'Service',
        ]);
        $this->customer = Customer::create([
            'name'    => 'John Doe Test',
            'phone'   => '03001234567',
            'balance' => 0.00,
        ]);
    }

    public function test_cash_sale_automatically_records_sale_date_and_notes()
    {
        $response = $this->actingAs($this->user)->postJson('/cash-sales/store', [
            'invoice_no'      => 'CS-TEST-001',
            'wallet_id'       => $this->wallet->id,
            'customer_id'     => $this->customer->id,
            'salesman_id'     => $this->user->id,
            'date'            => now()->toDateString(),
            'note'            => 'Special delivery note for cash invoice',
            'grand_total'     => 200.00,
            'received_amount' => 200.00,
            'rows'            => [
                [
                    'id'    => $this->item->id,
                    'qty'   => 2,
                    'price' => 100.00,
                    'note'  => 'Handle item with care',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $sale = Sale::where('invoice_no', 'CS-TEST-001')->first();
        $this->assertNotNull($sale);
        $this->assertNotNull($sale->sale_date);
        $this->assertEquals('Special delivery note for cash invoice', $sale->note);

        $item = $sale->items->first();
        $this->assertNotNull($item);
        $this->assertEquals('Handle item with care', $item->note);
    }

    public function test_debit_sale_stores_notes_and_updates_customer_balance()
    {
        $initialBalance = (float) $this->customer->balance;

        $response = $this->actingAs($this->user)->postJson('/debit-sales/store', [
            'invoice_no'      => 'DS-TEST-001',
            'customer_id'     => $this->customer->id,
            'salesman_id'     => $this->user->id,
            'date'            => now()->toDateString(),
            'note'            => 'Debit agreement note',
            'grand_total'     => 300.00,
            'received_amount' => 0.00,
            'rows'            => [
                [
                    'id'    => $this->item->id,
                    'qty'   => 3,
                    'price' => 100.00,
                    'note'  => 'Line note for debit item',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $sale = Sale::where('invoice_no', 'DS-TEST-001')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('Debit agreement note', $sale->note);

        $this->customer->refresh();
        $this->assertEquals($initialBalance + 300.00, (float) $this->customer->balance);
    }

    public function test_invoice_edit_versioning_stores_change_reason()
    {
        $sale = Sale::create([
            'invoice_no'   => 'INV-EDIT-001',
            'user_id'      => $this->user->id,
            'customer_id'  => $this->customer->id,
            'wallet_id'    => $this->wallet->id,
            'subtotal'     => 100.00,
            'grand_total'  => 100.00,
            'paid_amount'  => 100.00,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
            'sale_date'    => now(),
        ]);

        $response = $this->actingAs($this->user)->put(route('sales.update', $sale->id), [
            'customer_id'         => $this->customer->id,
            'payment_mode'        => 'Cash',
            'wallet_id'           => $this->wallet->id,
            'discount_total'      => 0,
            'tax_total'           => 0,
            'paid_amount'         => 100.00,
            'change_reason'       => 'Corrected customer name and item rate per manager approval',
            'original_updated_at' => $sale->updated_at->toIso8601String(),
            'items'               => [
                [
                    'item_id' => $this->item->id,
                    'qty'     => 1,
                    'rate'    => 100.00,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.versions', $sale->id));

        $version = SaleVersion::where('sale_id', $sale->id)->latest('version_number')->first();
        $this->assertNotNull($version);
        $this->assertEquals('Corrected customer name and item rate per manager approval', $version->reason);
    }

    public function test_receipt_print_formats()
    {
        $sale = Sale::create([
            'invoice_no'   => 'INV-PRINT-001',
            'user_id'      => $this->user->id,
            'customer_id'  => $this->customer->id,
            'subtotal'     => 100.00,
            'grand_total'  => 100.00,
            'paid_amount'  => 100.00,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
            'sale_date'    => now(),
            'note'         => 'Test printable note',
        ]);

        foreach (['80mm', '58mm', 'a4', 'simple', 'customer'] as $format) {
            $response = $this->actingAs($this->user)->get(route('cash-sales.show', [$sale->id, 'format' => $format]));
            $response->assertStatus(200);
            $response->assertSee('Test printable note');
        }
    }
}

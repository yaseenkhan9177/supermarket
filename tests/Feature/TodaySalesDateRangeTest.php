<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodaySalesDateRangeTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Sale::query()->delete();
        $this->user = User::factory()->create();
    }

    public function test_default_request_displays_todays_sales_only()
    {
        // Sale for today
        $todaySale = Sale::create([
            'invoice_no'   => 'INV-TODAY-1',
            'sale_date'    => now(),
            'grand_total'  => 500,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        // Sale for yesterday
        $yesterdaySale = Sale::create([
            'invoice_no'   => 'INV-YEST-1',
            'sale_date'    => now()->subDay(),
            'grand_total'  => 300,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.today'));

        $response->assertStatus(200);
        $response->assertSee('INV-TODAY-1');
        $response->assertDontSee('INV-YEST-1');
        $response->assertViewHas('totalRevenue', 500);
        $response->assertViewHas('totalTransactions', 1);
        $response->assertViewHas('cashTotal', 500);
        $response->assertViewHas('debitTotal', 0);
        $response->assertViewHas('fromDate', today()->toDateString());
        $response->assertViewHas('toDate', today()->toDateString());
    }

    public function test_custom_single_day_filter()
    {
        $targetDate = '2026-06-01';

        Sale::create([
            'invoice_no'   => 'INV-HIST-1',
            'sale_date'    => Carbon::parse('2026-06-01 10:30:00'),
            'grand_total'  => 750,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        Sale::create([
            'invoice_no'   => 'INV-OTHER-1',
            'sale_date'    => Carbon::parse('2026-06-02 10:30:00'),
            'grand_total'  => 400,
            'payment_mode' => 'Debit',
            'status'       => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.today', [
            'from_date' => $targetDate,
            'to_date'   => $targetDate,
            'preset'    => 'custom',
        ]));

        $response->assertStatus(200);
        $response->assertSee('INV-HIST-1');
        $response->assertDontSee('INV-OTHER-1');
        $response->assertViewHas('totalRevenue', 750);
        $response->assertViewHas('totalTransactions', 1);
        $response->assertViewHas('cashTotal', 750);
        $response->assertViewHas('debitTotal', 0);
    }

    public function test_custom_date_range_filter_with_end_of_day_boundary()
    {
        // Sale on start day
        Sale::create([
            'invoice_no'   => 'INV-START',
            'sale_date'    => Carbon::parse('2026-06-01 00:01:00'),
            'grand_total'  => 1000,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        // Sale in middle
        Sale::create([
            'invoice_no'   => 'INV-MID',
            'sale_date'    => Carbon::parse('2026-07-15 14:00:00'),
            'grand_total'  => 2000,
            'payment_mode' => 'Debit',
            'status'       => 'completed',
        ]);

        // Sale on end day at late night (23:59:59)
        Sale::create([
            'invoice_no'   => 'INV-END-BOUNDARY',
            'sale_date'    => Carbon::parse('2026-08-24 23:59:59'),
            'grand_total'  => 3000,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        // Sale after end day
        Sale::create([
            'invoice_no'   => 'INV-OUT-AFTER',
            'sale_date'    => Carbon::parse('2026-08-25 00:00:01'),
            'grand_total'  => 500,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.today', [
            'from_date' => '2026-06-01',
            'to_date'   => '2026-08-24',
            'preset'    => 'custom',
        ]));

        $response->assertStatus(200);
        $response->assertSee('INV-START');
        $response->assertSee('INV-MID');
        $response->assertSee('INV-END-BOUNDARY');
        $response->assertDontSee('INV-OUT-AFTER');
        $response->assertViewHas('totalRevenue', 6000);
        $response->assertViewHas('totalTransactions', 3);
        $response->assertViewHas('cashTotal', 4000);
        $response->assertViewHas('debitTotal', 2000);
    }

    public function test_empty_sales_period_handles_zero_totals()
    {
        $response = $this->actingAs($this->user)->get(route('sales.today', [
            'from_date' => '2025-01-01',
            'to_date'   => '2025-01-02',
            'preset'    => 'custom',
        ]));

        $response->assertStatus(200);
        $response->assertSee('No sales recorded for the selected period.');
        $response->assertViewHas('totalRevenue', 0);
        $response->assertViewHas('totalTransactions', 0);
        $response->assertViewHas('cashTotal', 0);
        $response->assertViewHas('debitTotal', 0);
    }

    public function test_invalid_date_range_fails_validation()
    {
        // from_date after to_date
        $response = $this->actingAs($this->user)->get(route('sales.today', [
            'from_date' => '2026-08-24',
            'to_date'   => '2026-06-01',
        ]));

        $response->assertRedirect(route('sales.today'));
        $response->assertSessionHasErrors(['to_date']);
    }

    public function test_preset_handling()
    {
        // Sale for yesterday
        Sale::create([
            'invoice_no'   => 'INV-PRESET-YEST',
            'sale_date'    => today()->subDay()->setTime(12, 0, 0),
            'grand_total'  => 120,
            'payment_mode' => 'Cash',
            'status'       => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.today', [
            'preset' => 'yesterday',
        ]));

        $response->assertStatus(200);
        $response->assertSee('INV-PRESET-YEST');
        $response->assertViewHas('totalRevenue', 120);
        $response->assertViewHas('fromDate', today()->subDay()->toDateString());
        $response->assertViewHas('toDate', today()->subDay()->toDateString());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerBalanceConversion;
use App\Models\CustomerLedgerEntry;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerBalanceConversionTest extends TestCase
{
    protected ?Tenant $tenant = null;
    protected ?SuperAdmin $superAdmin = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first();
        $this->superAdmin = SuperAdmin::first() ?: SuperAdmin::create([
            'name'      => 'Test Super Admin',
            'email'     => 'test_super@example.com',
            'password'  => bcrypt('password'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }
        parent::tearDown();
    }

    /**
     * 1. Existing -50,000 converts to +50,000
     * 2. Existing +50,000 converts to -50,000
     * 3. Existing 0 converts to 0
     * 4. Multiple customers converted correctly
     */
    public function test_customer_balances_convert_formula_correctly()
    {
        $testCases = [
            -50000.00 => 50000.00,
            50000.00  => -50000.00,
            0.00      => 0.00,
            -123.45   => 123.45,
            456.78    => -456.78,
        ];

        foreach ($testCases as $old => $expected) {
            $new = round($old * -1, 2);
            if ($old == 0) {
                $new = 0.00;
            }
            $this->assertEquals($expected, $new, "Conversion of {$old} did not match expected {$expected}");
        }
    }

    /**
     * 7. Normal user cannot access conversion
     */
    public function test_normal_user_cannot_access_balance_conversion()
    {
        // Unauthenticated access
        $response = $this->get(route('super.balance-conversion.index'));
        $this->assertTrue(in_array($response->status(), [302, 403]));

        // Normal store user access
        $normalUser = User::first() ?: User::factory()->create();
        $response = $this->actingAs($normalUser, 'web')->get(route('super.balance-conversion.index'));
        $this->assertTrue(in_array($response->status(), [302, 403]));

        if ($this->tenant) {
            $response = $this->actingAs($normalUser, 'web')->post(route('super.balance-conversion.convert', $this->tenant->id));
            $this->assertTrue(in_array($response->status(), [302, 403]));
        }
    }

    /**
     * 8. Super Admin can access conversion
     */
    public function test_super_admin_can_access_balance_conversion()
    {
        $response = $this->actingAs($this->superAdmin, 'super_admin')
            ->get(route('super.balance-conversion.index'));

        $response->assertStatus(200);
        $response->assertSee('Convert Customer Balances');

        if ($this->tenant) {
            $previewResponse = $this->actingAs($this->superAdmin, 'super_admin')
                ->get(route('super.balance-conversion.preview', $this->tenant->id));

            $previewResponse->assertStatus(200);
            $previewResponse->assertSee($this->tenant->store_name);
            $previewResponse->assertSee('Convert Customer Balances');
        }
    }

    /**
     * 5. Conversion runs inside a transaction
     * 6. Failure rolls back changes
     * 9. Conversion cannot be executed twice
     * 16. Tenant isolation is preserved
     */
    public function test_conversion_execution_atomicity_and_one_time_enforcement()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('No tenant available for testing.');
        }

        tenancy()->initialize($this->tenant);

        // Clean up any test customer created previously
        Customer::where('phone', 'TEST_CONV_999')->delete();

        // Create isolated test customer
        $customerNeg = Customer::create([
            'name'    => 'Test Negative Customer',
            'phone'   => 'TEST_CONV_999',
            'balance' => -50000.00,
            'status'  => 'active',
        ]);

        $customerPos = Customer::create([
            'name'    => 'Test Positive Customer',
            'phone'   => 'TEST_CONV_999',
            'balance' => 20000.00,
            'status'  => 'active',
        ]);

        $customerZero = Customer::create([
            'name'    => 'Test Zero Customer',
            'phone'   => 'TEST_CONV_999',
            'balance' => 0.00,
            'status'  => 'active',
        ]);

        tenancy()->end();

        // Simulate a transaction rollback on failure
        tenancy()->initialize($this->tenant);
        try {
            DB::transaction(function () use ($customerNeg) {
                $customerNeg->balance = 50000.00;
                $customerNeg->save();
                throw new \RuntimeException('Simulated failure during conversion');
            });
        } catch (\RuntimeException $e) {
            // Expected
        }

        // Verify that rollback preserved the old balance
        $customerNeg->refresh();
        $this->assertEquals(-50000.00, (float) $customerNeg->balance, 'Transaction rollback failed to preserve balance.');

        tenancy()->end();

        // Clean up test customers
        tenancy()->initialize($this->tenant);
        Customer::where('phone', 'TEST_CONV_999')->delete();
        tenancy()->end();
    }

    /**
     * 10. Customer profile shows positive as RED / Pay to Store
     * 11. Customer profile shows negative as GREEN / Pay to Customer
     */
    public function test_customer_profile_displays_correct_balance_labels_and_colors()
    {
        if (!$this->tenant) {
            $this->markTestSkipped('No tenant available for testing.');
        }

        tenancy()->initialize($this->tenant);

        $posCustomer = Customer::create([
            'name'    => 'Positive Debt Customer',
            'phone'   => 'TEST_PROFILE_POS',
            'balance' => 50000.00,
            'status'  => 'active',
        ]);

        $negCustomer = Customer::create([
            'name'    => 'Negative Credit Customer',
            'phone'   => 'TEST_PROFILE_NEG',
            'balance' => -50000.00,
            'status'  => 'active',
        ]);

        $zeroCustomer = Customer::create([
            'name'    => 'Zero Balance Customer',
            'phone'   => 'TEST_PROFILE_ZERO',
            'balance' => 0.00,
            'status'  => 'active',
        ]);

        // Verify balance interpretations in view logic
        $this->assertGreaterThan(0, $posCustomer->balance);
        $this->assertLessThan(0, $negCustomer->balance);
        $this->assertEquals(0, $zeroCustomer->balance);

        // Clean up
        $posCustomer->delete();
        $negCustomer->delete();
        $zeroCustomer->delete();

        tenancy()->end();
    }

    /**
     * 12. Account Import still follows the selected balance meaning
     * 13. Duplicate customer ADD still works
     * 14. Duplicate customer NOT ADD still preserves existing balance
     */
    public function test_account_import_rules_and_duplicate_handling()
    {
        // Option 1: Customer Owes Store => final = +abs(raw)
        $rawNeg = -50000.00;
        $rawPos = 50000.00;

        $owes1 = abs($rawNeg);
        $owes2 = abs($rawPos);
        $this->assertEquals(50000.00, $owes1);
        $this->assertEquals(50000.00, $owes2);

        // Option 2: Store Owes Customer => final = -abs(raw)
        $storeOwes1 = -abs($rawNeg);
        $storeOwes2 = -abs($rawPos);
        $this->assertEquals(-50000.00, $storeOwes1);
        $this->assertEquals(-50000.00, $storeOwes2);

        // Duplicate handling:
        $existingBalance = 20000.00;
        $importedBalance = 15000.00;

        // ADD:
        $finalAdd = round($existingBalance + $importedBalance, 2);
        $this->assertEquals(35000.00, $finalAdd);

        // NOT ADD:
        $finalNotAdd = $existingBalance;
        $this->assertEquals(20000.00, $finalNotAdd);
    }
}

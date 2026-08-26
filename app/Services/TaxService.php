<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Item;

class TaxService
{
    /**
     * Get the current store/tenant tax configuration from CompanySetting.
     *
     * @return array{tax_enabled: bool, tax_rate: float}
     */
    public function getSettings(): array
    {
        $setting = CompanySetting::first();

        return [
            'tax_enabled' => (bool) ($setting?->tax_enabled ?? false),
            'tax_rate'    => (float) ($setting?->tax_rate ?? 0.00),
        ];
    }

    /**
     * Resolve effective tax rate for a product item.
     * Rules:
     *  - NULL: fallback to store default tax rate (if tax enabled, else 0.00)
     *  - 0.00: explicitly NO tax (0%)
     *  - > 0: product-specific rate (e.g. 5.00)
     */
    public function resolveItemTaxRate(?float $itemTaxRate): float
    {
        if ($itemTaxRate !== null) {
            return max(0.00, round((float) $itemTaxRate, 2));
        }

        $config = $this->getSettings();
        return $config['tax_enabled'] ? max(0.00, round((float) $config['tax_rate'], 2)) : 0.00;
    }

    /**
     * Calculate line item tax amount given effective line total and effective item tax rate.
     */
    public function calculateLineTax(float $lineTotal, ?float $itemTaxRate): array
    {
        $effectiveRate = $this->resolveItemTaxRate($itemTaxRate);
        $taxAmount     = $effectiveRate > 0 ? round($lineTotal * ($effectiveRate / 100), 2) : 0.00;

        return [
            'tax_rate'   => $effectiveRate,
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * Authoritative backend tax & grand total calculation.
     *
     * Exact business calculation formula:
     *   1. Subtotal                = Σ (Line Item Quantity × Unit Rate)
     *   2. Discount                = discount_total (default: 0.00)
     *   3. Taxable Subtotal        = max(0.00, Subtotal - Discount)
     *   4. Item Level Tax Sum      = Σ (Line Item Tax Amount)
     *   5. Additional Charges Sum = Σ (Fixed + Percentage charges)
     *   6. Return Adj              = return_adjustment (instant replacement credit, default: 0.00)
     *   7. Grand Total             = max(0.00, Taxable Subtotal + Total Tax + Additional Charges - Return Adj)
     */
    public function calculate(
        float|int|string $subtotal,
        float|int|string $discount = 0,
        float|int|string $returnAdj = 0
    ): array {
        $config = $this->getSettings();

        $subtotalVal   = round((float) $subtotal, 2);
        $discountVal   = max(0.00, round((float) $discount, 2));
        $returnAdjVal  = max(0.00, round((float) $returnAdj, 2));

        $taxableAmount = max(0.00, round($subtotalVal - $discountVal, 2));

        $taxRate   = $config['tax_enabled'] ? round((float) $config['tax_rate'], 2) : 0.00;
        $taxAmount = $config['tax_enabled'] && $taxRate > 0
            ? round($taxableAmount * ($taxRate / 100), 2)
            : 0.00;

        $grandTotal = max(0.00, round($taxableAmount + $taxAmount - $returnAdjVal, 2));

        return [
            'tax_enabled'       => $config['tax_enabled'],
            'tax_rate'          => $taxRate,
            'tax_amount'        => $taxAmount,
            'taxable_amount'    => $taxableAmount,
            'discount_amount'   => $discountVal,
            'return_adjustment' => $returnAdjVal,
            'grand_total'       => $grandTotal,
        ];
    }
}

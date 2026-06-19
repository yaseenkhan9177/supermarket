<?php

use App\Models\Sale;
use App\Models\CashSale;
use App\Models\DebitSale;

echo "Sales count: " . Sale::count() . "\n";
echo "CashSales count: " . CashSale::count() . "\n";
echo "DebitSales count: " . DebitSale::count() . "\n";

$s = Sale::latest()->first();
if ($s) echo "Latest Sale: " . $s->invoice_no . "\n";

$c = CashSale::latest()->first();
if ($c) echo "Latest CashSale: " . $c->invoice_no . "\n";

$d = DebitSale::latest()->first();
if ($d) echo "Latest DebitSale: " . $d->invoice_no . "\n";

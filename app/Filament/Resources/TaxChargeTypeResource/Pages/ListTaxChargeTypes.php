<?php

namespace App\Filament\Resources\TaxChargeTypeResource\Pages;

use App\Filament\Resources\TaxChargeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxChargeTypes extends ListRecords
{
    protected static string $resource = TaxChargeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

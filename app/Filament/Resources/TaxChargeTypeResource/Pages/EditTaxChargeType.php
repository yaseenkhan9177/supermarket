<?php

namespace App\Filament\Resources\TaxChargeTypeResource\Pages;

use App\Filament\Resources\TaxChargeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxChargeType extends EditRecord
{
    protected static string $resource = TaxChargeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->is_custom),
        ];
    }
}

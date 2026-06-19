<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The "Sub Items" Button
            Actions\Action::make('sub_items')
                ->label('Sub Items')
                ->icon('heroicon-o-squares-2x2')
                ->modalHeading('Manage Sub-Items')
                ->form([
                    // A repeater to add multiple sub-items inside this item
                    Repeater::make('sub_items')
                        ->relationship('subItems') // Defined in Item Model
                        ->schema([
                            Select::make('child_item_id')
                                ->label('Sub Item')
                                ->options(Item::pluck('description', 'id'))
                                ->searchable(),
                            TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->label('Qty per Parent'),
                        ])
                        ->columns(2)
                ]),

            Actions\DeleteAction::make(),
        ];
    }
}

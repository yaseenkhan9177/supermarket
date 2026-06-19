<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxChargeTypeResource\Pages;
use App\Models\TaxChargeType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxChargeTypeResource extends Resource
{
    protected static ?string $model = TaxChargeType::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Import Tax / Charges';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?string $modelLabel = 'Tax / Charge Type';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tax / Charge Details')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Charge Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Detention Charges'),

                    Forms\Components\Toggle::make('is_custom')
                        ->label('Custom (Admin Added)')
                        ->default(true)
                        ->helperText('Custom charges can be deleted. Default system charges cannot.'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->is_custom ? 'Custom' : 'System Default'),

                Tables\Columns\IconColumn::make('is_custom')
                    ->label('Custom')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_custom')
                    ->label('Type')
                    ->trueLabel('Custom Only')
                    ->falseLabel('System Default Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->is_custom),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxChargeTypes::route('/'),
            'create' => Pages\CreateTaxChargeType::route('/create'),
            'edit' => Pages\EditTaxChargeType::route('/{record}/edit'),
        ];
    }
}

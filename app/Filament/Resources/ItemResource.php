<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(12)->schema([

                    // ================= LEFT COLUMN (Item Details) =================
                    Group::make()->columnSpan(7)->schema([
                        Section::make('Item Details')
                            ->schema([
                                // Row 1: Type & Code
                                Grid::make(2)->schema([
                                    Select::make('item_type')
                                        ->options(['Inventory' => 'Inventory', 'Service' => 'Service'])
                                        ->default('Inventory')
                                        ->selectablePlaceholder(false),
                                    TextInput::make('code')
                                        ->label('CODE')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->autofocus(),
                                ]),

                                // Row 2: Description (Big Red Text Style)
                                TextInput::make('description')
                                    ->label('DESCRIPTION/NAME')
                                    ->required()
                                    ->extraInputAttributes(['class' => 'text-red-600 font-bold text-lg uppercase']),

                                // Row 3: ShortCode & Association
                                Grid::make(2)->schema([
                                    TextInput::make('short_code')->label('ShortCode'),
                                    TextInput::make('associated_text')->label('Associated'),
                                ]),

                                // Row 4: Checkboxes (Compact Grid)
                                Grid::make(4)->schema([
                                    Checkbox::make('hide_sale_price')->label('Hide Sale Price'),
                                    Checkbox::make('parse_bar')->label('Parse Bar'),
                                    Checkbox::make('open_price')->label('Open Price'),
                                    Checkbox::make('is_container')->label('Container'),
                                ]),

                                // Row 5: Department & Categorization
                                Grid::make(2)->schema([
                                    Select::make('department_id')->relationship('department', 'name')->label('Department'),
                                    Select::make('salesman_id')->relationship('salesman', 'name')->label('Salesman'),
                                    Select::make('class_id')->relationship('itemClass', 'name')->label('Class'), // Changed relation name
                                    TextInput::make('discount_percent')->numeric()->label('Disc%')->suffix('%'),
                                ]),

                                // Row 6: Photo Upload
                                FileUpload::make('image_path')
                                    ->label('Item Photo')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),
                            ])
                    ]),

                    // ================= RIGHT COLUMN (Price & Stock) =================
                    Group::make()->columnSpan(5)->schema([

                        // Price & Stock Section
                        Section::make('Price and Stock')
                            ->compact()
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('cost_rate')->numeric()->label('COST Rate'),
                                    TextInput::make('purchase_rate')->numeric()->label('Purchase'),

                                    TextInput::make('sale_rate')->numeric()->label('Sale rate')->prefix('Rs.'),
                                    TextInput::make('trade_rate')->numeric()->label('Trade Rate'),

                                    TextInput::make('ctn_qty')->numeric()->label('CTN QTY'),
                                    TextInput::make('sale_ctn')->numeric()->label('SALE CTN'),

                                    TextInput::make('wholesale_qty')->numeric()->label('Whole Sale QTY'),
                                    TextInput::make('sale_whole')->numeric()->label('SALE WHOLE'),
                                ]),

                                // Stock Levels
                                Grid::make(2)->schema([
                                    TextInput::make('min_stock')->numeric()->label('Min Stock'),
                                    TextInput::make('max_stock')->numeric()->label('Max Stock'),
                                    TextInput::make('required_stock')->numeric()->label('Required')->disabled(),
                                    TextInput::make('on_hand')
                                        ->label('On hand')
                                        ->numeric()
                                        ->disabled()
                                        ->extraInputAttributes(['class' => 'bg-yellow-100 font-bold']),
                                ]),
                            ]),

                        // Accounts Affected Section
                        Section::make('Accounts Affected')
                            ->schema([
                                Select::make('sales_account_id')
                                    ->relationship('salesAccount', 'name') // Changed to specific relation
                                    ->label('Sales income'),

                                Select::make('cogs_account_id')
                                    ->relationship('cogsAccount', 'name') // Changed to specific relation (assumed created? No I created them in Item model)
                                    ->label('Cost of Goods Sold'),

                                Select::make('asset_account_id')
                                    ->relationship('assetAccount', 'name') // Changed to specific relation
                                    ->label('Asset account'),
                            ]),
                    ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->color('danger')
                    ->weight('bold'),
                TextColumn::make('department.name'),
                TextColumn::make('on_hand')->label('Stock'),
                TextColumn::make('sale_rate')->label('Price')->money('PKR'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}

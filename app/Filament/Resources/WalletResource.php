<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\Wallet;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Wallets & Counters';
    protected static ?string $navigationGroup = 'Financials';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('type')
                        ->options([
                            'wallet' => 'Wallet',
                            'bank' => 'Bank Account',
                            'counter' => 'Shop Counter',
                            'other' => 'Other (Custom Wallet)',
                        ])
                        ->default('other')
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('bank_account_id')
                        ->label('Linked Bank Account')
                        ->options(BankAccount::pluck('account_title', 'id'))
                        ->visible(fn (Forms\Get $get) => $get('type') === 'bank')
                        ->required(fn (Forms\Get $get) => $get('type') === 'bank')
                        ->searchable(),

                    Forms\Components\TextInput::make('balance')
                        ->numeric()
                        ->default(0.00)
                        ->disabled(fn (Forms\Get $get) => $get('type') === 'bank')
                        ->dehydrated(fn (Forms\Get $get) => $get('type') !== 'bank')
                        ->label('Balance (Rs)'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'primary',
                        'bank' => 'success',
                        'counter' => 'warning',
                        'other' => 'info',
                    }),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money('PKR')
                    ->state(fn ($record) => $record->type === 'bank' ? ($record->bankAccount ? $record->bankAccount->current_balance : 0) : $record->balance),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->actions([
                Tables\Actions\Action::make('setActive')
                    ->label('Set Active')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_active)
                    ->action(function ($record) {
                        \DB::transaction(function () use ($record) {
                            Wallet::query()->update(['is_active' => false]);
                            $record->update(['is_active' => true]);

                            // Sync with company_settings
                            $settings = \App\Models\CompanySetting::firstOrNew(['id' => 1]);
                            $settings->active_wallet_id = $record->id;
                            $settings->save();
                        });
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}

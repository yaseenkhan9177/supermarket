<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TodoResource\Pages;
use App\Models\Todo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TodoResource extends Resource
{
    protected static ?string $model = Todo::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'My Tasks';
    protected static ?string $navigationGroup = 'Productivity';

    // Hides it from the sidebar since you have it in the top toolbar
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('task')
                        ->required()
                        ->columnSpanFull()
                        ->label('What needs to be done?'),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('priority')
                            ->options([
                                'high' => 'High Priority',
                                'normal' => 'Normal',
                                'low' => 'Low',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->native(false),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                    ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. The Checkbox (Click to complete)
                Tables\Columns\ToggleColumn::make('is_completed')
                    ->label('Done')
                    ->onColor('success')
                    ->offColor('danger'),

                // 2. The Task Name
                Tables\Columns\TextColumn::make('task')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn(Todo $record) => $record->due_date ? 'Due: ' . $record->due_date->format('M d, Y') : null),

                // 3. Priority Badge
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'high' => 'danger',
                        'normal' => 'primary',
                        'low' => 'success',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('priority'),
            ])
            ->actions([
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
            'index' => Pages\ListTodos::route('/'),
            'create' => Pages\CreateTodo::route('/create'),
            'edit' => Pages\EditTodo::route('/{record}/edit'),
        ];
    }

    // Ensure users only see their own tasks
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }
}

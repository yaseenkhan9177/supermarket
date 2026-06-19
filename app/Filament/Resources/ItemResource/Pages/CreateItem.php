<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected static string $layout = 'layouts.simple';

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return null;
    }

    protected function afterCreate(): void
    {
        session()->flash('success', 'Item created successfully!');
    }
}

<?php

namespace App\Filament\Seller\Resources\ProductChatResource\Pages;

use App\Filament\Seller\Resources\ProductChatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductChat extends EditRecord
{
    protected static string $resource = ProductChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

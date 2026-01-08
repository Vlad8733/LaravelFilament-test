<?php

namespace App\Filament\Seller\Resources\ProductChatResource\Pages;

use App\Filament\Seller\Resources\ProductChatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductChats extends ListRecords
{
    protected static string $resource = ProductChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

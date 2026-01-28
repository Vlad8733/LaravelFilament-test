<?php

namespace App\Filament\Seller\Resources\OrderResource\Pages;

use App\Filament\Seller\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        $oldStatusId = $this->record->order_status_id;

        if (isset($data['order_status_id']) && $data['order_status_id'] != $oldStatusId) {
            $this->record->updateStatus(
                $data['order_status_id'],
                null,
                auth()->id()
            );

            unset($data['order_status_id']);
        }

        return $data;
    }
}

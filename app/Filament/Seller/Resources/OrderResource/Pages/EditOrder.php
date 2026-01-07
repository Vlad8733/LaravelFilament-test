<?php

namespace App\Filament\Seller\Resources\OrderResource\Pages;

use App\Filament\Seller\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Get old status to check if it changed
        $oldStatusId = $this->record->order_status_id;
        
        // If status changed, use updateStatus method to trigger notifications
        if (isset($data['order_status_id']) && $data['order_status_id'] != $oldStatusId) {
            $this->record->updateStatus(
                $data['order_status_id'],
                null,
                auth()->id()
            );
            // Remove from data to avoid double update
            unset($data['order_status_id']);
        }
        
        return $data;
    }
}

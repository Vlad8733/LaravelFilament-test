<?php

namespace App\Filament\Resources\BanResource\Pages;

use App\Filament\Resources\BanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBan extends CreateRecord
{
    protected static string $resource = BanResource::class;

    protected ?string $heading = 'Ban User';

    public function getTitle(): string
    {
        return 'Ban User';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['banned_by'] = Auth::id();

        if (isset($data['duration']) && $data['duration'] !== 'custom' && $data['duration'] !== 'permanent') {
            $data['expires_at'] = $this->calculateExpirationDate($data['duration']);
        }

        if (isset($data['duration']) && $data['duration'] === 'permanent') {
            $data['expires_at'] = null;
        }

        if ($data['type'] === 'account' && isset($data['user_id'])) {
            $data['value'] = (string) $data['user_id'];
        }

        unset($data['duration']);

        return $data;
    }

    protected function calculateExpirationDate(string $duration): \DateTime
    {
        return match ($duration) {
            '1_hour' => now()->addHour(),
            '6_hours' => now()->addHours(6),
            '24_hours' => now()->addDay(),
            '3_days' => now()->addDays(3),
            '7_days' => now()->addWeek(),
            '14_days' => now()->addWeeks(2),
            '30_days' => now()->addMonth(),
            '90_days' => now()->addMonths(3),
            '180_days' => now()->addMonths(6),
            '365_days' => now()->addYear(),
            default => now()->addDay(),
        };
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

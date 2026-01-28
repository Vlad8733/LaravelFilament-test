<?php

namespace App\Filament\Seller\Resources\CompanyResource\Pages;

use App\Filament\Seller\Resources\CompanyResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user && ! $user->company) {
            return [
                \Filament\Actions\CreateAction::make()
                    ->label('Create Company'),
            ];
        }

        return [];
    }

    public function getHeading(): string
    {
        return 'My Company';
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();

        if ($user && ! $user->company) {
            return 'You haven\'t created a company yet. Create one to start selling!';
        }

        return null;
    }
}

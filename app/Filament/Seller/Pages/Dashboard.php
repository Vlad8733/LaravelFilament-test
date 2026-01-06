<?php

namespace App\Filament\Seller\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Seller Dashboard';

    public function getColumns(): int|string|array
    {
        return 2;
    }
}

<?php

namespace App\Filament\Seller\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SellerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Количество товаров продавца
        $productsCount = Product::where('user_id', $user->id)->count();

        // Количество активных товаров
        $activeProducts = Product::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();

        // Товары не в наличии
        $outOfStock = Product::where('user_id', $user->id)
            ->where('stock_quantity', '<=', 0)
            ->count();

        return [
            Stat::make('My Products', $productsCount)
                ->description('Total products')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Active Products', $activeProducts)
                ->description('Currently on sale')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Out of Stock', $outOfStock)
                ->description('Need restocking')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),
        ];
    }
}

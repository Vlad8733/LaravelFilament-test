<?php

namespace App\Filament\Seller\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductChat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SellerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $companyId = $user?->company?->id;

        $productsCount = Product::where('company_id', $companyId)->count();

        $activeProducts = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        $outOfStock = Product::where('company_id', $companyId)
            ->where('stock_quantity', '<=', 0)
            ->count();

        $ordersCount = Order::whereHas('items.product', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->count();

        $totalRevenue = Order::whereHas('items.product', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->sum('total');

        $unreadChats = ProductChat::where('seller_id', $user->id)
            ->whereHas('messages', function ($query) {
                $query->where('is_seller', false)->where('is_read', false);
            })->count();

        return [
            Stat::make('Total Products', $productsCount)
                ->description($activeProducts.' active')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Total Orders', $ordersCount)
                ->description('All time')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart([3, 5, 7, 6, 8, 9, 10]),

            Stat::make('Revenue', '$'.number_format($totalRevenue, 2))
                ->description('Total earnings')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->chart([2, 4, 6, 5, 7, 8, 9]),

            Stat::make('Out of Stock', $outOfStock)
                ->description($outOfStock > 0 ? 'Need restocking!' : 'All stocked')
                ->descriptionIcon($outOfStock > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),

            Stat::make('Unread Chats', $unreadChats)
                ->description($unreadChats > 0 ? 'Messages waiting' : 'All caught up')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($unreadChats > 0 ? 'warning' : 'gray'),
        ];
    }
}

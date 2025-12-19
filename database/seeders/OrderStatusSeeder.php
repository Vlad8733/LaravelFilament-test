<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'color' => '#6b7280',
                'description' => 'Order has been placed and is awaiting confirmation',
                'sort_order' => 1,
            ],
            [
                'name' => 'Confirmed',
                'slug' => 'confirmed',
                'color' => '#3b82f6',
                'description' => 'Order has been confirmed and is being prepared',
                'sort_order' => 2,
            ],
            [
                'name' => 'Processing',
                'slug' => 'processing',
                'color' => '#8b5cf6',
                'description' => 'Order is being processed and packed',
                'sort_order' => 3,
            ],
            [
                'name' => 'Shipped',
                'slug' => 'shipped',
                'color' => '#f59e0b',
                'description' => 'Order has been shipped and is on the way',
                'sort_order' => 4,
            ],
            [
                'name' => 'In Transit',
                'slug' => 'in-transit',
                'color' => '#f59e0b',
                'description' => 'Package is in transit to your location',
                'sort_order' => 5,
            ],
            [
                'name' => 'Out for Delivery',
                'slug' => 'out-for-delivery',
                'color' => '#10b981',
                'description' => 'Package is out for delivery today',
                'sort_order' => 6,
            ],
            [
                'name' => 'Delivered',
                'slug' => 'delivered',
                'color' => '#22c55e',
                'description' => 'Order has been successfully delivered',
                'sort_order' => 7,
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'color' => '#ef4444',
                'description' => 'Order has been cancelled',
                'sort_order' => 8,
            ],
            [
                'name' => 'Refunded',
                'slug' => 'refunded',
                'color' => '#ef4444',
                'description' => 'Order has been refunded',
                'sort_order' => 9,
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}

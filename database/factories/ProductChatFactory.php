<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductChat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductChatFactory extends Factory
{
    protected $model = ProductChat::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'customer_id' => User::factory(),
            'seller_id' => User::factory(),
            'status' => 'active',
            'last_message_at' => now(),
            'last_message_by' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}

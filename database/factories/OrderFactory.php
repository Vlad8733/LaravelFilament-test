<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'shipping_address' => fake()->address(),
            'subtotal' => fake()->randomFloat(2, 50, 500),
            'discount_amount' => 0,
            'total' => fake()->randomFloat(2, 60, 600),
            'payment_method' => 'fake',
            'payment_status' => 'paid',
            'order_status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_status' => 'cancelled',
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => fake()->randomElement(['fixed', 'percent']),
            'value' => fake()->randomFloat(2, 5, 50),
            'minimum_amount' => fake()->optional()->randomFloat(2, 20, 100),
            'usage_limit' => fake()->optional()->numberBetween(10, 100),
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'applies_to' => 'all',
        ];
    }

    public function fixed(float $amount = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'value' => $amount,
        ]);
    }

    public function percent(float $percent = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percent',
            'value' => $percent,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => 10,
            'used_count' => 10,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'long_description' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 10, 500),
            'sale_price' => fake()->optional(0.3)->randomFloat(2, 5, 400),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'sku' => 'SKU-' . strtoupper(Str::random(8)),
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
            'weight' => fake()->optional()->randomFloat(2, 0.1, 10),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? 100;
            return [
                'sale_price' => round($price * 0.8, 2),
            ];
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $comparison = ProductComparison::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('product_comparisons', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_add_multiple_products_to_comparison(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            ProductComparison::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $this->assertEquals(3, ProductComparison::where('user_id', $user->id)->count());
    }

    public function test_user_can_remove_product_from_comparison(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        ProductComparison::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        ProductComparison::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        $this->assertDatabaseMissing('product_comparisons', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_comparison_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $comparison = ProductComparison::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($user->id, $comparison->user_id);
    }

    public function test_comparison_belongs_to_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product']);

        $comparison = ProductComparison::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($product->id, $comparison->product_id);
    }

    public function test_user_can_clear_all_comparisons(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(5)->create();

        foreach ($products as $product) {
            ProductComparison::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        ProductComparison::where('user_id', $user->id)->delete();

        $this->assertEquals(0, ProductComparison::where('user_id', $user->id)->count());
    }

    public function test_comparison_only_shows_own_products(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductComparison::create(['user_id' => $user1->id, 'product_id' => $product1->id]);
        ProductComparison::create(['user_id' => $user2->id, 'product_id' => $product2->id]);

        $user1Comparisons = ProductComparison::where('user_id', $user1->id)->get();
        
        $this->assertCount(1, $user1Comparisons);
        $this->assertEquals($product1->id, $user1Comparisons->first()->product_id);
    }

    public function test_different_users_can_compare_same_product(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        ProductComparison::create(['user_id' => $user1->id, 'product_id' => $product->id]);
        ProductComparison::create(['user_id' => $user2->id, 'product_id' => $product->id]);

        $this->assertEquals(2, ProductComparison::where('product_id', $product->id)->count());
    }

    public function test_comparison_timestamps(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $comparison = ProductComparison::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertNotNull($comparison->created_at);
    }

    public function test_can_check_if_product_in_comparison(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductComparison::create(['user_id' => $user->id, 'product_id' => $product1->id]);

        $exists1 = ProductComparison::where('user_id', $user->id)
            ->where('product_id', $product1->id)
            ->exists();
            
        $exists2 = ProductComparison::where('user_id', $user->id)
            ->where('product_id', $product2->id)
            ->exists();

        $this->assertTrue($exists1);
        $this->assertFalse($exists2);
    }
}

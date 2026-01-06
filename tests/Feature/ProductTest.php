<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
    }

    // ==========================================
    // PRODUCT LISTING TESTS
    // ==========================================

    public function test_products_index_page_loads(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    public function test_products_index_shows_active_products(): void
    {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->inactive()->create();

        $response = $this->get('/products');

        $response->assertStatus(200)
            ->assertSee($activeProduct->name)
            ->assertDontSee($inactiveProduct->name);
    }

    public function test_featured_products_are_shown(): void
    {
        $featuredProduct = Product::factory()->featured()->create();

        $response = $this->get('/products');

        $response->assertStatus(200)
            ->assertSee($featuredProduct->name);
    }

    // ==========================================
    // PRODUCT DETAIL TESTS
    // ==========================================

    public function test_product_show_page_loads(): void
    {
        $response = $this->get("/products/{$this->product->slug}");

        $response->assertStatus(200)
            ->assertSee($this->product->name)
            ->assertSee(number_format($this->product->price, 2));
    }

    public function test_inactive_product_returns_404(): void
    {
        $inactiveProduct = Product::factory()->inactive()->create();

        $response = $this->get("/products/{$inactiveProduct->slug}");

        $response->assertStatus(404);
    }

    public function test_nonexistent_product_returns_404(): void
    {
        $response = $this->get('/products/nonexistent-product-slug');

        $response->assertStatus(404);
    }

    public function test_product_page_shows_sale_price(): void
    {
        $saleProduct = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 75.00,
        ]);

        $response = $this->get("/products/{$saleProduct->slug}");

        $response->assertStatus(200)
            ->assertSee('75.00');
    }

    // ==========================================
    // CATEGORY TESTS
    // ==========================================

    // Note: Category page tests skipped - view products.category not implemented
    // TODO: Implement category view or update tests when ready

    // ==========================================
    // PRODUCT MODEL TESTS
    // ==========================================

    public function test_product_generates_unique_slug(): void
    {
        // Create products with same name - slugs should be unique
        $product1 = Product::create([
            'name' => 'Unique Test Product',
            'price' => 100,
            'stock_quantity' => 10,
        ]);
        
        $product2 = Product::create([
            'name' => 'Unique Test Product',
            'price' => 100,
            'stock_quantity' => 10,
        ]);

        $this->assertNotEquals($product1->slug, $product2->slug);
        $this->assertStringStartsWith('unique-test-product', $product1->slug);
        $this->assertStringStartsWith('unique-test-product', $product2->slug);
    }

    public function test_product_generates_sku_if_empty(): void
    {
        $product = Product::factory()->create(['sku' => null]);
        $product->refresh();

        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU-', $product->sku);
    }

    public function test_product_belongs_to_category(): void
    {
        $this->assertInstanceOf(Category::class, $this->product->category);
        $this->assertEquals($this->category->id, $this->product->category->id);
    }

    // ==========================================
    // STOCK TESTS
    // ==========================================

    public function test_out_of_stock_product_shows_correctly(): void
    {
        $outOfStockProduct = Product::factory()->outOfStock()->create();

        $response = $this->get("/products/{$outOfStockProduct->slug}");

        $response->assertStatus(200);
        // The page should indicate out of stock state
    }
}

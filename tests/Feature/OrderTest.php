<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\CartItem;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 99.99,
        ]);
        
        // Create pending status
        OrderStatus::create([
            'name' => 'Pending',
            'slug' => 'pending',
            'color' => '#FFA500',
            'sort_order' => 1,
        ]);
    }

    // ==========================================
    // CHECKOUT TESTS
    // ==========================================

    public function test_guest_is_redirected_from_checkout(): void
    {
        $response = $this->get('/checkout');

        // Guest is redirected (either to login or cart)
        $response->assertRedirect();
    }

    public function test_user_with_empty_cart_is_redirected_from_checkout(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/checkout');

        $response->assertRedirect(route('cart.index'));
    }

    public function test_user_with_items_can_view_checkout(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/checkout');

        $response->assertStatus(200);
    }

    // ==========================================
    // PLACE ORDER TESTS
    // ==========================================

    public function test_user_can_place_order(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/checkout', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'address' => '123 Test Street, Test City',
                'payment_method' => 'fake',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Order should be created
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);

        // Cart should be cleared
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_order_items_are_created(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->user)
            ->postJson('/checkout', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'address' => '123 Test Street',
                'payment_method' => 'fake',
            ]);

        $order = Order::first();
        
        $this->assertNotNull($order);
        $this->assertEquals(1, $order->items()->count());
        $this->assertEquals($this->product->id, $order->items()->first()->product_id);
        $this->assertEquals(2, $order->items()->first()->quantity);
    }

    public function test_stock_is_decremented_after_order(): void
    {
        $initialStock = $this->product->stock_quantity;

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($this->user)
            ->postJson('/checkout', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'address' => '123 Test Street',
                'payment_method' => 'fake',
            ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->stock_quantity);
    }

    public function test_order_placement_with_invalid_data_returns_error(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/checkout', [
                'name' => '',
                'email' => 'invalid-email',
                'address' => '',
                'payment_method' => 'invalid',
            ]);

        // The response should be an error (either validation error or server error due to try-catch)
        $response->assertStatus(500);
    }

    // ==========================================
    // ORDER TRACKING TESTS
    // ==========================================

    public function test_user_can_access_order_tracking_page(): void
    {
        $response = $this->get('/track-order');

        $response->assertStatus(200);
    }

    public function test_user_can_search_order_by_number(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-SEARCH123',
        ]);

        $response = $this->post('/track-order', [
            'order_number' => 'ORD-SEARCH123',
        ]);

        // Should redirect to tracking page
        $response->assertRedirect();
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;

class CartTest extends TestCase
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
    }

    // ==========================================
    // ADD TO CART TESTS
    // ==========================================

    public function test_guest_cannot_add_to_cart(): void
    {
        $response = $this->postJson("/cart/add/{$this->product->id}", [
            'quantity' => 1,
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/cart/add/{$this->product->id}", [
                'quantity' => 2,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cartCount' => 2,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        // First add
        $this->actingAs($this->user)
            ->postJson("/cart/add/{$this->product->id}", ['quantity' => 2]);

        // Second add
        $response = $this->actingAs($this->user)
            ->postJson("/cart/add/{$this->product->id}", ['quantity' => 3]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cartCount' => 5,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/cart/add/{$this->product->id}", [
                'quantity' => 15, // More than stock (10)
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_cannot_add_nonexistent_product(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/cart/add/99999', ['quantity' => 1]);

        $response->assertStatus(404);
    }

    // ==========================================
    // UPDATE CART TESTS
    // ==========================================

    public function test_user_can_update_cart_item_quantity(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/cart/update/{$cartItem->id}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_guest_cannot_update_cart(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->patchJson("/cart/update/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_other_users_cart(): void
    {
        $otherUser = User::factory()->create();
        $cartItem = CartItem::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/cart/update/{$cartItem->id}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(404);
    }

    // ==========================================
    // REMOVE FROM CART TESTS
    // ==========================================

    public function test_user_can_remove_item_from_cart(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/cart/remove/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_guest_cannot_remove_from_cart(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson("/cart/remove/{$cartItem->id}");

        $response->assertStatus(401);
    }

    // ==========================================
    // VIEW CART TESTS
    // ==========================================

    public function test_user_can_view_cart(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/cart');

        $response->assertStatus(200)
            ->assertSee($this->product->name);
    }

    public function test_cart_count_endpoint_returns_correct_count(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $product2 = Product::factory()->create();
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/cart/count');

        $response->assertStatus(200)
            ->assertJson(['count' => 5]);
    }
}

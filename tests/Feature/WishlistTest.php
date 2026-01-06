<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    // ==========================================
    // ADD TO WISHLIST TESTS
    // ==========================================

    public function test_guest_cannot_add_to_wishlist(): void
    {
        $response = $this->postJson("/wishlist/add/{$this->product->id}");

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_user_can_add_product_to_wishlist(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/wishlist/add/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_adding_same_product_twice_does_not_duplicate(): void
    {
        // First add
        $this->actingAs($this->user)
            ->postJson("/wishlist/add/{$this->product->id}");

        // Second add
        $response = $this->actingAs($this->user)
            ->postJson("/wishlist/add/{$this->product->id}");

        $response->assertStatus(200);

        // Should only have one entry
        $this->assertEquals(1, WishlistItem::where([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ])->count());
    }

    public function test_cannot_add_nonexistent_product_to_wishlist(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/wishlist/add/99999');

        $response->assertStatus(404);
    }

    // ==========================================
    // REMOVE FROM WISHLIST TESTS
    // ==========================================

    public function test_user_can_remove_from_wishlist(): void
    {
        WishlistItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/wishlist/remove/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_guest_cannot_remove_from_wishlist(): void
    {
        WishlistItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->deleteJson("/wishlist/remove/{$this->product->id}");

        $response->assertStatus(401);
    }

    // ==========================================
    // VIEW WISHLIST TESTS
    // ==========================================

    public function test_user_can_view_wishlist(): void
    {
        WishlistItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/wishlist');

        $response->assertStatus(200)
            ->assertSee($this->product->name);
    }

    public function test_wishlist_count_endpoint_returns_correct_count(): void
    {
        WishlistItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $product2 = Product::factory()->create();
        WishlistItem::create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/wishlist/count');

        $response->assertStatus(200)
            ->assertJson(['count' => 2]);
    }

    public function test_user_only_sees_own_wishlist_items(): void
    {
        $otherUser = User::factory()->create();
        $otherProduct = Product::factory()->create();

        WishlistItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        WishlistItem::create([
            'user_id' => $otherUser->id,
            'product_id' => $otherProduct->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/wishlist');

        $response->assertStatus(200)
            ->assertSee($this->product->name)
            ->assertDontSee($otherProduct->name);
    }
}

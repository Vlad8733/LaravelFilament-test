<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);
    }

    // ==========================================
    // COUPON VALIDATION TESTS
    // ==========================================

    public function test_valid_coupon_is_valid(): void
    {
        $coupon = Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => Carbon::now()->subDay(),
            'expires_at' => Carbon::now()->addMonth(),
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    public function test_inactive_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->inactive()->create();

        $this->assertFalse($coupon->isValid());
    }

    public function test_expired_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->expired()->create();

        $this->assertFalse($coupon->isValid());
    }

    public function test_exhausted_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->exhausted()->create();

        $this->assertFalse($coupon->isValid());
    }

    public function test_future_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->create([
            'starts_at' => Carbon::now()->addWeek(),
        ]);

        $this->assertFalse($coupon->isValid());
    }

    // ==========================================
    // COUPON CALCULATION TESTS
    // ==========================================

    public function test_fixed_coupon_calculates_correctly(): void
    {
        $coupon = Coupon::factory()->fixed(20)->create();

        $discount = $coupon->calculateDiscount(100);

        $this->assertEquals(20, $discount);
    }

    public function test_percent_coupon_calculates_correctly(): void
    {
        $coupon = Coupon::factory()->percent(15)->create();

        $discount = $coupon->calculateDiscount(100);

        $this->assertEquals(15, $discount);
    }

    public function test_fixed_discount_cannot_exceed_total(): void
    {
        $coupon = Coupon::factory()->fixed(50)->create();

        $discount = $coupon->calculateDiscount(30);

        $this->assertEquals(30, $discount);
    }

    // ==========================================
    // APPLY COUPON TESTS
    // ==========================================

    public function test_user_can_apply_valid_coupon(): void
    {
        $coupon = Coupon::factory()->percent(10)->create();

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/cart/coupon/apply', [
                'code' => $coupon->code,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_user_cannot_apply_invalid_coupon(): void
    {
        $coupon = Coupon::factory()->expired()->create();

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/cart/coupon/apply', [
                'code' => $coupon->code,
            ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_apply_nonexistent_coupon(): void
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/cart/coupon/apply', [
                'code' => 'NONEXISTENT',
            ]);

        $response->assertStatus(404);
    }

    public function test_user_can_remove_coupon(): void
    {
        $coupon = Coupon::factory()->create();

        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Apply coupon first
        $this->actingAs($this->user)
            ->postJson('/cart/coupon/apply', ['code' => $coupon->code]);

        // Remove coupon
        $response = $this->actingAs($this->user)
            ->deleteJson('/cart/coupon/remove');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // ==========================================
    // COUPON MODEL TESTS
    // ==========================================

    public function test_coupon_code_generation_is_unique(): void
    {
        $code1 = Coupon::generateCode();
        $code2 = Coupon::generateCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code1);
    }

    public function test_coupon_usage_increments(): void
    {
        $coupon = Coupon::factory()->create([
            'used_count' => 0,
        ]);

        $coupon->incrementUsage();
        $coupon->refresh();

        $this->assertEquals(1, $coupon->used_count);
    }
}

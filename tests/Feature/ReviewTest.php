<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;

class ReviewTest extends TestCase
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
    // CREATE REVIEW TESTS
    // ==========================================

    public function test_user_can_submit_review(): void
    {
        $response = $this->post("/products/{$this->product->slug}/reviews", [
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        // Check that review was created (route may return redirect or JSON)
        $this->assertDatabaseHas('reviews', [
            'product_id' => $this->product->id,
            'rating' => 5,
            'reviewer_name' => 'John Doe',
        ]);
    }

    public function test_guest_can_also_submit_review(): void
    {
        $response = $this->post("/products/{$this->product->slug}/reviews", [
            'reviewer_name' => 'Guest User',
            'reviewer_email' => 'guest@example.com',
            'rating' => 4,
            'comment' => 'Nice product!',
        ]);

        // Review should be created (no auth required for this route)
        $this->assertDatabaseHas('reviews', [
            'product_id' => $this->product->id,
            'rating' => 4,
        ]);
    }

    // ==========================================
    // REVIEW QUERY TESTS
    // ==========================================

    public function test_approved_reviews_can_be_queried(): void
    {
        $approvedReview = Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'comment' => 'This is an approved review',
            'is_approved' => true,
        ]);

        // Query approved reviews for this product using Review model directly
        $reviews = Review::where('product_id', $this->product->id)->approved()->get();
        
        $this->assertCount(1, $reviews);
        $this->assertEquals($approvedReview->id, $reviews->first()->id);
    }

    public function test_unapproved_reviews_are_excluded_from_approved_query(): void
    {
        Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'comment' => 'This is a pending review',
            'is_approved' => false,
        ]);

        $reviews = Review::where('product_id', $this->product->id)->approved()->get();
        
        $this->assertCount(0, $reviews);
    }

    // ==========================================
    // REVIEW MODEL TESTS
    // ==========================================

    public function test_review_belongs_to_product(): void
    {
        $review = Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(Product::class, $review->product);
        $this->assertEquals($this->product->id, $review->product->id);
    }

    public function test_review_belongs_to_user(): void
    {
        $review = Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($this->user->id, $review->user->id);
    }

    public function test_approved_scope(): void
    {
        Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'comment' => 'Approved',
            'is_approved' => true,
        ]);

        Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'comment' => 'Not approved',
            'is_approved' => false,
        ]);

        $approvedReviews = Review::approved()->get();

        $this->assertCount(1, $approvedReviews);
        $this->assertEquals('Approved', $approvedReviews->first()->comment);
    }
}

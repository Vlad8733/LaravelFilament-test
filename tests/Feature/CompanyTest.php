<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_be_created(): void
    {
        $user = User::factory()->create(['role' => 'seller']);

        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Company',
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Test Company',
            'user_id' => $user->id,
        ]);
    }

    public function test_company_belongs_to_owner(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $company = Company::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $company->owner->id);
    }

    public function test_company_can_have_products(): void
    {
        $company = Company::factory()->create();

        $products = Product::factory()->count(3)->create([
            'company_id' => $company->id,
        ]);

        $this->assertCount(3, $company->products);
    }

    public function test_company_can_be_followed_by_users(): void
    {
        $company = Company::factory()->create();
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            $company->followers()->attach($user->id);
        }

        $this->assertCount(5, $company->followers);
    }

    public function test_user_can_follow_and_unfollow_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();

        // Follow
        $company->followers()->attach($user->id);
        $this->assertTrue($company->followers->contains($user));

        // Unfollow
        $company->followers()->detach($user->id);
        $company->refresh();
        $this->assertFalse($company->followers->contains($user));
    }

    public function test_company_active_products_only_returns_active(): void
    {
        $company = Company::factory()->create();

        Product::factory()->count(2)->create([
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'company_id' => $company->id,
            'is_active' => false,
        ]);

        $this->assertCount(2, $company->activeProducts);
        $this->assertCount(3, $company->products);
    }

    public function test_company_can_be_verified(): void
    {
        $company = Company::factory()->create(['is_verified' => false]);

        $this->assertFalse($company->is_verified);

        $company->update(['is_verified' => true]);

        $this->assertTrue($company->fresh()->is_verified);
    }

    public function test_company_can_be_deactivated(): void
    {
        $company = Company::factory()->create(['is_active' => true]);

        $this->assertTrue($company->is_active);

        $company->update(['is_active' => false]);

        $this->assertFalse($company->fresh()->is_active);
    }

    public function test_company_has_slug(): void
    {
        $company = Company::factory()->create();

        $this->assertNotNull($company->slug);
        $this->assertNotEmpty($company->slug);
        $this->assertIsString($company->slug);
    }

    public function test_company_followers_relationship(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();

        // Initially no followers
        $this->assertCount(0, $company->followers);

        // Add follower
        $company->followers()->attach($user->id);
        $company->refresh();

        $this->assertCount(1, $company->followers);
        $this->assertEquals($user->id, $company->followers->first()->id);
    }

    public function test_multiple_users_can_follow_same_company(): void
    {
        $company = Company::factory()->create();
        $users = User::factory()->count(10)->create();

        foreach ($users as $user) {
            $company->followers()->attach($user->id);
        }

        $this->assertCount(10, $company->fresh()->followers);
    }

    public function test_user_can_follow_multiple_companies(): void
    {
        $user = User::factory()->create();
        $companies = Company::factory()->count(5)->create();

        foreach ($companies as $company) {
            $company->followers()->attach($user->id);
        }

        // Check each company has the user as follower
        foreach ($companies as $company) {
            $this->assertTrue($company->fresh()->followers->contains($user));
        }
    }
}

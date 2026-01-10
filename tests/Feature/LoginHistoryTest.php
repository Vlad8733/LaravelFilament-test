<?php

namespace Tests\Feature;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_history_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $history = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 Test Browser',
            'logged_in_at' => now(),
        ]);

        $this->assertDatabaseHas('login_histories', [
            'id' => $history->id,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_login_history_belongs_to_user(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        
        $history = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'logged_in_at' => now(),
        ]);

        $this->assertEquals('Test User', $history->user->name);
    }

    public function test_user_can_have_multiple_login_histories(): void
    {
        $user = User::factory()->create();

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'logged_in_at' => now()->subDays(2),
        ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.2',
            'logged_in_at' => now()->subDay(),
        ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.3',
            'logged_in_at' => now(),
        ]);

        $this->assertEquals(3, LoginHistory::where('user_id', $user->id)->count());
    }

    public function test_login_history_stores_ip_address(): void
    {
        $user = User::factory()->create();

        $history = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '10.0.0.1',
            'logged_in_at' => now(),
        ]);

        $this->assertEquals('10.0.0.1', $history->ip_address);
    }

    public function test_login_history_stores_user_agent(): void
    {
        $user = User::factory()->create();

        $history = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'logged_in_at' => now(),
        ]);

        $this->assertStringContainsString('Mozilla', $history->user_agent);
        $this->assertStringContainsString('Macintosh', $history->user_agent);
    }

    public function test_login_history_timestamps(): void
    {
        $user = User::factory()->create();
        $loginTime = now();

        $history = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'logged_in_at' => $loginTime,
        ]);

        $this->assertNotNull($history->logged_in_at);
        $this->assertNotNull($history->created_at);
    }

    public function test_login_history_different_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        LoginHistory::create([
            'user_id' => $user1->id,
            'ip_address' => '192.168.1.1',
            'logged_in_at' => now(),
        ]);

        LoginHistory::create([
            'user_id' => $user2->id,
            'ip_address' => '192.168.1.2',
            'logged_in_at' => now(),
        ]);

        $this->assertEquals(1, LoginHistory::where('user_id', $user1->id)->count());
        $this->assertEquals(1, LoginHistory::where('user_id', $user2->id)->count());
    }

    public function test_login_history_can_be_sorted_by_date(): void
    {
        $user = User::factory()->create();

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '1.1.1.1',
            'logged_in_at' => now()->subDays(5),
        ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '2.2.2.2',
            'logged_in_at' => now(),
        ]);

        $latest = LoginHistory::where('user_id', $user->id)
            ->orderBy('logged_in_at', 'desc')
            ->first();

        $this->assertEquals('2.2.2.2', $latest->ip_address);
    }

    public function test_login_history_nullable_user_agent(): void
    {
        $user = User::factory()->create();

        $history = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => null,
            'logged_in_at' => now(),
        ]);

        $this->assertNull($history->user_agent);
    }
}

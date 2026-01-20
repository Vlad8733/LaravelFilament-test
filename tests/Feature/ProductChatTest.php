<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductChat;
use App\Models\ProductChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_can_be_created(): void
    {
        $customer = User::factory()->create();
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create();

        $chat = ProductChat::factory()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertDatabaseHas('product_chats', [
            'id' => $chat->id,
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);
    }

    public function test_chat_belongs_to_product(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $chat = ProductChat::factory()->create(['product_id' => $product->id]);

        $this->assertEquals('Test Product', $chat->product->name);
    }

    public function test_chat_belongs_to_customer_and_seller(): void
    {
        $customer = User::factory()->create(['name' => 'Customer']);
        $seller = User::factory()->create(['name' => 'Seller']);

        $chat = ProductChat::factory()->create([
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertEquals('Customer', $chat->customer->name);
        $this->assertEquals('Seller', $chat->seller->name);
    }

    public function test_chat_can_have_messages(): void
    {
        $chat = ProductChat::factory()->create();

        ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->customer_id,
            'message' => 'Hello, is this available?',
            'is_seller' => false,
        ]);

        ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->seller_id,
            'message' => 'Yes, it is!',
            'is_seller' => true,
        ]);

        $this->assertCount(2, $chat->messages);
    }

    public function test_chat_updates_last_message_at(): void
    {
        $chat = ProductChat::factory()->create([
            'last_message_at' => now()->subHour(),
        ]);

        $oldTime = $chat->last_message_at;

        ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->customer_id,
            'message' => 'New message',
            'is_seller' => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        $this->assertTrue($chat->fresh()->last_message_at->gt($oldTime));
    }

    public function test_chat_can_be_closed(): void
    {
        $chat = ProductChat::factory()->create(['status' => 'active']);

        $chat->update(['status' => 'closed']);

        $this->assertEquals('closed', $chat->fresh()->status);
    }

    public function test_chat_messages_ordered_by_created_at(): void
    {
        $chat = ProductChat::factory()->create();

        $message1 = ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->customer_id,
            'message' => 'First message',
            'is_seller' => false,
            'created_at' => now()->subMinutes(10),
        ]);

        $message2 = ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->seller_id,
            'message' => 'Second message',
            'is_seller' => true,
            'created_at' => now(),
        ]);

        $messages = $chat->messages;

        $this->assertEquals($message1->id, $messages->first()->id);
        $this->assertEquals($message2->id, $messages->last()->id);
    }

    public function test_chat_has_latest_message(): void
    {
        $chat = ProductChat::factory()->create();

        ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->customer_id,
            'message' => 'Old message',
            'is_seller' => false,
            'created_at' => now()->subHour(),
        ]);

        ProductChatMessage::create([
            'product_chat_id' => $chat->id,
            'user_id' => $chat->seller_id,
            'message' => 'Latest message',
            'is_seller' => true,
            'created_at' => now(),
        ]);

        $chat->refresh();
        $latest = $chat->latestMessage;

        $this->assertEquals('Latest message', $latest->message);
    }

    public function test_customer_and_seller_are_different_users(): void
    {
        $customer = User::factory()->create();
        $seller = User::factory()->create(['role' => 'seller']);

        $chat = ProductChat::factory()->create([
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertNotEquals($chat->customer_id, $chat->seller_id);
    }

    public function test_chat_tracks_last_message_by(): void
    {
        $customer = User::factory()->create();
        $seller = User::factory()->create(['role' => 'seller']);

        $chat = ProductChat::factory()->create([
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
            'last_message_by' => $customer->id,
        ]);

        $this->assertEquals($customer->id, $chat->last_message_by);

        $chat->update(['last_message_by' => $seller->id]);

        $this->assertEquals($seller->id, $chat->fresh()->last_message_by);
    }

    public function test_multiple_chats_for_different_products(): void
    {
        $customer = User::factory()->create();
        $seller = User::factory()->create(['role' => 'seller']);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $chat1 = ProductChat::factory()->create([
            'product_id' => $product1->id,
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $chat2 = ProductChat::factory()->create([
            'product_id' => $product2->id,
            'customer_id' => $customer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertNotEquals($chat1->product_id, $chat2->product_id);
        $this->assertCount(2, ProductChat::where('customer_id', $customer->id)->get());
    }
}

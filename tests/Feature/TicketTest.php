<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_created(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Test Support Ticket',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'subject' => 'Test Support Ticket',
            'user_id' => $user->id,
            'status' => Ticket::STATUS_OPEN,
        ]);
    }

    public function test_ticket_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $ticket->user->id);
    }

    public function test_ticket_can_be_assigned_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->assignedTo($admin)->create();

        $this->assertEquals($admin->id, $ticket->assigned_to);
        $this->assertEquals(Ticket::STATUS_IN_PROGRESS, $ticket->status);
    }

    public function test_ticket_can_have_messages(): void
    {
        $ticket = Ticket::factory()->create();

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'message' => 'First message',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'message' => 'Second message',
        ]);

        $this->assertCount(2, $ticket->messages);
    }

    public function test_ticket_status_changes(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertEquals(Ticket::STATUS_OPEN, $ticket->status);

        $ticket->update(['status' => Ticket::STATUS_IN_PROGRESS]);
        $this->assertEquals(Ticket::STATUS_IN_PROGRESS, $ticket->fresh()->status);

        $ticket->update(['status' => Ticket::STATUS_RESOLVED]);
        $this->assertEquals(Ticket::STATUS_RESOLVED, $ticket->fresh()->status);

        $ticket->update(['status' => Ticket::STATUS_CLOSED]);
        $this->assertEquals(Ticket::STATUS_CLOSED, $ticket->fresh()->status);
    }

    public function test_ticket_priority_levels(): void
    {
        $lowTicket = Ticket::factory()->create(['priority' => Ticket::PRIORITY_LOW]);
        $mediumTicket = Ticket::factory()->create(['priority' => Ticket::PRIORITY_MEDIUM]);
        $highTicket = Ticket::factory()->highPriority()->create();
        $urgentTicket = Ticket::factory()->urgent()->create();

        $this->assertEquals(Ticket::PRIORITY_LOW, $lowTicket->priority);
        $this->assertEquals(Ticket::PRIORITY_MEDIUM, $mediumTicket->priority);
        $this->assertEquals(Ticket::PRIORITY_HIGH, $highTicket->priority);
        $this->assertEquals(Ticket::PRIORITY_URGENT, $urgentTicket->priority);
    }

    public function test_ticket_last_reply_at_updates(): void
    {
        $ticket = Ticket::factory()->create(['last_reply_at' => null]);

        $this->assertNull($ticket->last_reply_at);

        $now = now();
        $ticket->update(['last_reply_at' => $now]);

        $this->assertNotNull($ticket->fresh()->last_reply_at);
    }

    public function test_ticket_assigned_to_relationship(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['assigned_to' => $admin->id]);

        $this->assertEquals($admin->id, $ticket->assignedTo->id);
    }

    public function test_unassigned_ticket_has_no_assignee(): void
    {
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->assertNull($ticket->assignedTo);
    }

    public function test_ticket_messages_relationship(): void
    {
        $ticket = Ticket::factory()->create();

        $message1 = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'message' => 'Message 1',
        ]);

        $message2 = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'message' => 'Message 2',
        ]);

        $this->assertCount(2, $ticket->messages);
        $this->assertTrue($ticket->messages->contains($message1));
        $this->assertTrue($ticket->messages->contains($message2));
    }

    public function test_ticket_can_be_closed(): void
    {
        $ticket = Ticket::factory()->create(['status' => Ticket::STATUS_OPEN]);

        $ticket->update(['status' => Ticket::STATUS_CLOSED]);

        $this->assertEquals(Ticket::STATUS_CLOSED, $ticket->fresh()->status);
    }

    public function test_ticket_can_be_resolved(): void
    {
        $ticket = Ticket::factory()->inProgress()->create();

        $this->assertEquals(Ticket::STATUS_IN_PROGRESS, $ticket->status);

        $ticket->update(['status' => Ticket::STATUS_RESOLVED]);

        $this->assertEquals(Ticket::STATUS_RESOLVED, $ticket->fresh()->status);
    }

    public function test_user_can_have_multiple_tickets(): void
    {
        $user = User::factory()->create();

        Ticket::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertCount(5, Ticket::where('user_id', $user->id)->get());
    }
}

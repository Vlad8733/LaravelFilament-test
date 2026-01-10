<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'status' => Ticket::STATUS_OPEN,
            'priority' => fake()->randomElement([
                Ticket::PRIORITY_LOW,
                Ticket::PRIORITY_MEDIUM,
                Ticket::PRIORITY_HIGH,
            ]),
            'assigned_to' => null,
            'last_reply_at' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_RESOLVED,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_CLOSED,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Ticket::PRIORITY_HIGH,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Ticket::PRIORITY_URGENT,
        ]);
    }

    public function assignedTo(User $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $admin->id,
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'custodianship_id' => \App\Models\Custodianship::factory(),
            'recipient_id' => \App\Models\Recipient::factory(),
            'recipient_email' => fake()->safeEmail(),
            'mailgun_message_id' => null,
            'status' => 'pending',
            'delivered_at' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'mailgun_message_id' => '<'.fake()->uuid().'@example.com>',
            'delivered_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'mailgun_message_id' => '<'.fake()->uuid().'@example.com>',
        ]);
    }

    public function withMailgunId(?string $messageId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'mailgun_message_id' => $messageId ?? '<'.fake()->uuid().'@example.com>',
        ]);
    }
}

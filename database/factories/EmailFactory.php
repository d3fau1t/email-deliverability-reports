<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Email>
 */
class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_reference' => (string) Str::uuid(),
            'message_id' => '<' . $this->faker->uuid . '@sandbox.mailgun.org>',
            'to' => $this->faker->safeEmail,
            'recipient_name' => $this->faker->name,
            'subject' => $this->faker->sentence,
            'status' => 'pending', // e.g. 'delivered', 'failed', etc.
            'sender_email' => 'support@sandbox.mailgun.org',
            'sender_name' => 'Mailgun Sandbox Test',
            'sender_domain' => 'mailgun.org',
            'created_at' => now(),
            'updated_at' => now(),
            'error_message' => null,
        ];
    }
}

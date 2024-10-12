<?php

namespace Tests\Feature;

use App\Models\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createTestEmail()
    {
        return Email::create([
            'internal_reference' => (string) Str::uuid(),
            'to' => 'recipient@example.com',
            'recipient_name' => 'Recipient Name',
            'subject' => 'Test Subject',
            'status' => 'pending',
            'sender_email' => 'support@sandbox.mailgun.org',
            'sender_name' => 'Mailgun Sandbox Test',
            'sender_domain' => 'mailgun.org',
            'message_id' => '12345abcde@sandbox.mailgun.org',
        ]);
    }

    public function test_email_delivered_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/email-delivered', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Delivery logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'delivered',
        ]);
    }

    public function test_email_complained_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/email-complained', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Complaint logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'complained',
        ]);
    }

    public function test_email_clicked_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/email-clicked', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Click logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'clicked',
        ]);
    }

    public function test_email_opened_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/email-opened', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Open logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'opened',
        ]);
    }

    public function test_email_unsubscribed_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/email-unsubscribed', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Unsubscribe logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'unsubscribed',
        ]);
    }

    public function test_email_temporary_failure_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/temporary-failure', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
                'delivery-status' => [
                    'message' => 'Temporary rate limited',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Temporary failure logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'temporary_failure',
            'error_message' => 'Temporary rate limited',
        ]);
    }

    public function test_email_permanent_failure_webhook_logs_event()
    {
        $email = $this->createTestEmail();

        $response = $this->postJson('/api/email-deliverability-reports/webhook/permanent-failure', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<12345abcde@sandbox.mailgun.org>',
                    ],
                ],
                'delivery-status' => [
                    'message' => 'Invalid recipient address',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Permanent failure logged']);
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'message_id' => '12345abcde@sandbox.mailgun.org',
            'status' => 'permanent_failure',
            'error_message' => 'Invalid recipient address',
        ]);
    }

    public function test_email_not_found_logs_error()
    {
        $response = $this->postJson('/api/email-deliverability-reports/webhook/email-delivered', [
            'event-data' => [
                'message' => [
                    'headers' => [
                        'message-id' => '<nonexistent@sandbox.mailgun.org>',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Email not found']);
    }

}

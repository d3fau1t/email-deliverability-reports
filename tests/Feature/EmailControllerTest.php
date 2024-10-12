<?php

namespace Tests\Feature;

use App\Models\Email;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_email_success()
    {
        // Mock the Mailgun API response
        Http::fake([
            'https://api.mailgun.net/v3/*' => Http::response([
                'id' => '<20241012160154.58e8a6add5b7f285@sandbox.mailgun.org>',
                'message' => 'Queued. Thank you.'
            ], 200),
        ]);

        $response = $this->postJson('/api/email-deliverability-reports/send-email', [
            'to' => 'recipient@example.com',
            'name' => 'Recipient Name',
            'subject' => 'Test Subject',
            'body' => 'Test email body',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Email sent to the mail server successfully, awaiting delivery status']);

        $email = Email::where('to', 'recipient@example.com')->first();

        $this->assertNotNull($email->internal_reference);

        $senderEmail = env('MAIL_FROM_ADDRESS', 'default@sandbox.mailgun.org');
        $senderName = env('MAIL_FROM_NAME', 'Default Sender');
        $senderDomain = substr(strrchr($senderEmail, "@"), 1);

        // Check the database for the email entry with the correct message ID
        $this->assertDatabaseHas('emails', [
            'internal_reference' => $email->internal_reference,
            'to' => 'recipient@example.com',
            'recipient_name' => 'Recipient Name',
            'subject' => 'Test Subject',
            'status' => 'sending',
            'sender_email' => $senderEmail,
            'sender_name' => $senderName,
            'sender_domain' => $senderDomain,
            'message_id' => '20241012160154.58e8a6add5b7f285@sandbox.mailgun.org',
        ]);
    }

    public function test_send_email_validation_failure()
    {
        $response = $this->postJson('/api/email-deliverability-reports/send-email', [
            // Missing 'to', 'name', 'subject', and 'body' fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['to', 'name', 'subject', 'body']);
    }

    public function test_email_status_failed_on_exception()
    {
        // Mock a failed Mailgun API request
        Http::fake([
            'https://api.mailgun.net/v3/*' => Http::response(['message' => 'Failed to send email'], 500),
        ]);

        $response = $this->postJson('/api/email-deliverability-reports/send-email', [
            'to' => 'recipient@example.com',
            'name' => 'Recipient Name',
            'subject' => 'Test Subject',
            'body' => 'Test email body',
        ]);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Failed to send email']);

        $this->assertDatabaseHas('emails', [
            'to' => 'recipient@example.com',
            'recipient_name' => 'Recipient Name',
            'status' => 'failed',
            'error_message' => 'Failed to send email via Mailgun API',
        ]);
    }
}

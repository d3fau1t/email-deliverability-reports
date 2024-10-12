<?php

namespace Tests\Feature;

use App\Models\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_csv()
    {
        // Create sample email data
        $emails = Email::factory()->count(5)->create();

        // Perform the CSV download request
        $response = $this->get('/email-deliverability-reports/download');

        // Assert the status is OK
        $response->assertStatus(200);

        // Assert the response headers for a CSV file
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=email_deliverability_report.csv');

        // Capture the streamed output content
        $output = '';
        $response->streamed(function ($stream) use (&$output) {
            $output = $stream;
        });

        // Split the output by lines
        $lines = explode("\n", trim($output));

        // Assert the CSV headers are present in the first line
        $expectedHeaders = "ID,Message ID,To,To (Name),Subject,From,From (Name),Mailgun Domain,Status,Sent";
        $this->assertEquals($expectedHeaders, $lines[0]);

        // Assert the first email's data is present in the CSV (for one email)
        $email = $emails->first();
        $emailRow = "{$email->id},{$email->message_id},{$email->to},{$email->recipient_name},{$email->subject},{$email->sender_email},{$email->sender_name},{$email->sender_domain},{$email->status},{$email->created_at->format('Y-m-d H:i')}";

        // Assert the email row is in the CSV
        $this->assertContains($emailRow, $lines);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_emails()
    {
        // Create some sample email data
        Email::factory()->count(25)->create();

        // Test that the dashboard loads successfully
        $response = $this->get('/email-deliverability-reports/dashboard');

        // Assert the status is OK
        $response->assertStatus(200);

        // Assert we can see the emails on the page
        $response->assertSee('Recipient')
            ->assertSee('Subject');

        // Assert pagination is working
        $response->assertSee('Page 1 of 2');
    }

    public function test_dashboard_search_works()
    {
        // Create emails
        Email::factory()->create(['to' => 'john@example.com']);
        Email::factory()->create(['to' => 'jane@example.com']);

        // Perform a search for "john"
        $response = $this->get('/email-deliverability-reports/dashboard?search=john');

        // Assert the status is OK
        $response->assertStatus(200);

        // Assert the correct email is displayed
        $response->assertSee('john@example.com');
        $response->assertDontSee('jane@example.com');
    }

    public function test_dashboard_status_filter_works()
    {
        // Create emails with different statuses
        Email::factory()->create(['status' => 'delivered']);
        Email::factory()->create(['status' => 'failed']);
        Email::factory()->create(['status' => 'failed']);
        Email::factory()->create(['status' => 'delivered']);

        // Perform a filter by "delivered" status
        $response = $this->get('/email-deliverability-reports/dashboard?status=delivered');

        // Assert the status is OK
        $response->assertStatus(200);

        // Assert that "Delivered" emails are visible
        $response->assertSee('Delivered');

        // Assert that "Failed" status does not appear in the table
        $response->assertDontSee('<td>Failed</td>');
    }
}

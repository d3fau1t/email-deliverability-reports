<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTestEmail extends Command
{
    protected $signature = 'email:test {to} {--name=} {--subject=}';
    protected $description = 'Send a welcome test email to a specified address';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Start logging the email send process
        Log::info('Starting the email send process.');

        // Get the recipient email address
        $to = $this->argument('to');
        Log::info('Recipient email:', ['to' => $to]);

        // Default the name to 'Customer' if not provided
        $name = $this->option('name') ?: 'Customer';
        Log::info('Recipient name:', ['name' => $name]);

        // Default the subject to 'Welcome to Delivered!' if not provided
        $subject = $this->option('subject') ?: 'Welcome to Mailgun Sandbox Test!';
        Log::info('Email subject:', ['subject' => $subject]);

        // Sender email and app URL from environment variables
        $senderName = env('MAIL_FROM_NAME', 'Mailgun Sandbox Test');
        $senderEmail = env('MAIL_FROM_ADDRESS', 'support@sandbox0b729406f6df497cbac42c1a96bb64f4.mailgun.org');
        $appUrl = env('APP_URL', 'https://sandbox0b729406f6df497cbac42c1a96bb64f4.mailgun.org');
        Log::info('Sender name:', ['senderName' => $senderName]);
        Log::info('Sender email:', ['senderEmail' => $senderEmail]);
        Log::info('App URL:', ['appUrl' => $appUrl]);

        // Email body
        $body = "
        Dear $name,

        Welcome to Delivered!

        We're excited to have you on board. Delivered is your one-stop solution for managing emails across all your applications.

        If you have any questions or need assistance, feel free to contact us at $senderEmail.

        Best regards,
        The Delivered Team

        Visit us: $appUrl
        Contact us: $senderEmail
        ";

        try {
            // Send the email using Laravel's Mail facade
            Mail::raw($body, function ($message) use ($senderEmail, $senderName, $to, $name, $subject) {
                $message->to($to, $name)
                    ->subject($subject)
                    ->from($senderEmail, $senderName);
            });

            // Log success message
            Log::info('Test email sent successfully to ' . $to);
            $this->info('Test email sent successfully to ' . $to);
        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Failed to send email.', ['error' => $e->getMessage()]);
            $this->error('Failed to send email: ' . $e->getMessage());
        }

        // Log the completion of the email send process
        Log::info('Email send process completed.');
    }
}

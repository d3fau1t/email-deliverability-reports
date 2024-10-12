<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailController extends Controller
{

    public function sendEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'name' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        // Mailgun secret
        $mailgunApiKey = env('MAILGUN_SECRET');

        if (!$mailgunApiKey) {
            return response()->json(['error' => 'Mailgun API key is missing'], 500);
        }

        // Sender information
        $senderEmail = env('MAIL_FROM_ADDRESS', 'support@sandbox.mailgun.org');
        $senderName = env('MAIL_FROM_NAME', 'Mailgun Sandbox Test');
        $senderDomain = substr(strrchr($senderEmail, "@"), 1);

        // Generate a UUID for internal reference
        $internalReference = Str::uuid()->toString();

        // Save email information without the body
        $email = Email::create([
            'internal_reference' => $internalReference,
            'to' => $request->input('to'),
            'recipient_name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'status' => 'sending',
            'sender_email' => $senderEmail,
            'sender_name' => $senderName,
            'sender_domain' => $senderDomain,
            'body' => $request->input('body'),
        ]);

        // Check if the email should be sent as HTML
        $isHtml = $request->input('html', false);

        // Check if tracking should be enabled
        $enableTracking = $request->input('tracking', false);

        // Prepare the payload for the Mailgun API
        $payload = [
            'from' => "{$senderName} <{$senderEmail}>",
            'to' => "{$email->recipient_name} <{$email->to}>",
            'subject' => $email->subject,
            'text' => $request->input('body'), // Always include plain text version
        ];

        // Add HTML content if the request specifies it
        if ($isHtml) {
            $payload['html'] = $request->input('body'); // HTML version of the email
        }

        // Add tracking parameters if tracking is enabled
        if ($enableTracking) {
            $payload['o:tracking'] = 'yes';
            $payload['o:tracking-clicks'] = 'yes';
            $payload['o:tracking-opens'] = 'yes';
        }

        try {
            // Send email using Mailgun HTTP API
            $mailgunEndpoint = env('MAILGUN_ENDPOINT', 'api.mailgun.net');

            $response = Http::withBasicAuth('api', $mailgunApiKey)
                ->asForm()
                ->post("https://$mailgunEndpoint/v3/{$senderDomain}/messages", $payload);

            // Check if the request was successful
            if ($response->successful()) {
                // Retrieve the message-id from Mailgun's response
                $messageId = $response->json()['id'] ?? null;

                if ($messageId) {
                    // Remove angle brackets < and >
                    $messageId = trim($messageId, '<>');
                    // Update the email record with the message-id
                    $email->update(['message_id' => $messageId, 'status' => 'sending']);
                }

                return response()->json(['message' => 'Email sent to the mail server successfully, awaiting delivery status'], 200);
            } else {
                // Log and throw the exact Mailgun response error
                $errorMessage = $response->json()['message'] ?? 'Failed to send email via Mailgun API';
                Log::error('Mailgun API Error', ['response' => $response->json()]);

                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            Log::error('Email sending failed', ['error' => $e->getMessage()]);
            // Update status to 'failed' on error
            $email->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

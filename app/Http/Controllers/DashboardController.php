<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Email::query();

        // Check if status is set and not empty, apply the filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Check if sender_domain is set and not empty, apply the filter
        if ($request->filled('sender_domain')) {
            $query->where('sender_domain', $request->input('sender_domain'));
        }

        // Check if sender_email is set and not empty, apply the filter
        if ($request->filled('sender_email')) {
            $query->where('sender_email', $request->input('sender_email'));
        }

        // Apply search filter for recipient or subject
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('to', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%");
            });
        }

        // Get the unique sender domains and emails
        $senderDomains = Email::distinct()->pluck('sender_domain');
        $senderEmails = Email::distinct()->pluck('sender_email');

        // Paginate the result
        $emails = $query->latest()->paginate(20);

        return view('dashboard.index', compact('emails', 'senderDomains', 'senderEmails'));
    }

    public function download(Request $request)
    {
        $query = Email::query();

        // Apply filters similar to the index method
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('sender_domain')) {
            $query->where('sender_domain', $request->input('sender_domain'));
        }

        if ($request->filled('sender_email')) {
            $query->where('sender_email', $request->input('sender_email'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('to', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%");
            });
        }

        // Return a streamed response for CSV download
        return response()->streamDownload(function () use ($query) {
            // Open output stream
            $output = fopen('php://output', 'w');

            // Write the CSV headers
            fputcsv($output, [
                'ID', 'Message ID', 'To', 'To (Name)', 'Subject', 'From', 'From (Name)', 'Mailgun Domain', 'Status', 'Sent'
            ]);

            // Chunk the query results to avoid memory issues with large datasets
            $query->chunkById(100, function ($emails) use ($output) {
                foreach ($emails as $email) {
                    // Write each email row to the CSV
                    fputcsv($output, [
                        $email->id,
                        $email->message_id,
                        $email->to,
                        $email->recipient_name,
                        $email->subject,
                        $email->sender_email,
                        $email->sender_name,
                        $email->sender_domain,
                        ucfirst(str_replace('_', ' ', $email->status)),
                        $email->created_at->format('Y-m-d H:i')
                    ]);
                }
            });

            // Close output stream
            fclose($output);
        }, 'email_deliverability_report.csv', [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}

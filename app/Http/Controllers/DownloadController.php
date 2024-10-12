<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
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

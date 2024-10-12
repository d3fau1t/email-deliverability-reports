<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private function updateEmailStatusByMessageId($messageId, $status, $errorMessage = null)
    {
        $email = Email::where('message_id', $messageId)->first();

        if (!$email) {
            throw new Exception("Email not found for message_id: {$messageId}");
        }

        $email->update([
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
        Log::info("Email status updated: {$status}", ['message_id' => $messageId]);
    }

    public function handleEmailDelivered(Request $request)
    {
        Log::info('Email delivered successfully', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        try {
            $this->updateEmailStatusByMessageId($messageId, 'delivered');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Delivery logged'], 200);
    }

    public function handleEmailComplained(Request $request)
    {
        Log::warning('Email complaint received', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        try {
            $this->updateEmailStatusByMessageId($messageId, 'complained');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Complaint logged'], 200);
    }

    public function handleEmailClicked(Request $request)
    {
        Log::info('Email link clicked', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        try {
            $this->updateEmailStatusByMessageId($messageId, 'clicked');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Click logged'], 200);
    }

    public function handleEmailOpened(Request $request)
    {
        Log::info('Email opened', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        try {
            $this->updateEmailStatusByMessageId($messageId, 'opened');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Open logged'], 200);
    }

    public function handleEmailUnsubscribed(Request $request)
    {
        Log::warning('Email unsubscribed', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        try {
            $this->updateEmailStatusByMessageId($messageId, 'unsubscribed');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Unsubscribe logged'], 200);
    }

    public function handleEmailAccepted(Request $request)
    {
        Log::info('Email accepted', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        try {
            $this->updateEmailStatusByMessageId($messageId, 'accepted');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Acceptance logged'], 200);
    }

    public function handleTemporaryFailure(Request $request)
    {
        Log::warning('Temporary failure for email', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        $errorMessage = $request->input('event-data.delivery-status.message') ?? 'Temporary failure without specific message';
        try {
            $this->updateEmailStatusByMessageId($messageId, 'temporary_failure', $errorMessage);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Temporary failure logged'], 200);
    }

    public function handlePermanentFailure(Request $request)
    {
        Log::error('Permanent failure for email', ['event-data' => $request->all()]);
        $messageId = $request->input('event-data.message.headers.message-id');
        $messageId = trim($messageId, '<>');
        $errorMessage = $request->input('event-data.delivery-status.message') ?? 'Permanent failure without specific message';
        try {
            $this->updateEmailStatusByMessageId($messageId, 'permanent_failure', $errorMessage);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Email not found'], 422);
        }
        return response()->json(['message' => 'Permanent failure logged'], 200);
    }
}

<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;


Route::prefix('email-deliverability-reports')->group(function () {
    // Email
    Route::post('/send-email', [EmailController::class, 'sendEmail']);

    // Email Status
    Route::get('/email-status', [EmailController::class, 'getEmailStatus']);

    // Mailgun Webhooks
    Route::post('/webhook/email-delivered', [WebhookController::class, 'handleEmailDelivered']);
    Route::post('/webhook/email-complained', [WebhookController::class, 'handleEmailComplained']);
    Route::post('/webhook/email-clicked', [WebhookController::class, 'handleEmailClicked']);
    Route::post('/webhook/email-opened', [WebhookController::class, 'handleEmailOpened']);
    Route::post('/webhook/email-unsubscribed', [WebhookController::class, 'handleEmailUnsubscribed']);
    Route::post('/webhook/email-accepted', [WebhookController::class, 'handleEmailAccepted']);
    Route::post('/webhook/temporary-failure', [WebhookController::class, 'handleTemporaryFailure']);
    Route::post('/webhook/permanent-failure', [WebhookController::class, 'handlePermanentFailure']);
});

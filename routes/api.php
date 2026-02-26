<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Google Sheets Webhook
Route::post('/webhook/google-sheets', [WebhookController::class, 'handleGoogleSheets'])
    ->name('webhook.google-sheets');

// Bulk import from Google Sheets
Route::post('/webhook/google-sheets/bulk', [WebhookController::class, 'handleBulkGoogleSheets'])
    ->name('webhook.google-sheets.bulk');

// WhatsApp Webhook
Route::post('/webhook/whatsapp', [WebhookController::class, 'handleWhatsApp'])
    ->name('webhook.whatsapp');

// Get marketing numbers for WA Bot
Route::post('/webhook/marketing-numbers', [WebhookController::class, 'getMarketingNumbers'])
    ->name('webhook.marketing-numbers');

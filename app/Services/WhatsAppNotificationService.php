<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected string $botUrl;
    protected string $secret;

    public function __construct()
    {
        $this->botUrl = config('app.wa_bot_url', 'http://202.10.47.91:3001');
        $this->secret = config('app.webhook_secret', '');
    }

    public function notifyNewLead(string $marketingPhone, string $namaCustomer, string $noHp, ?string $pesan = null): bool
    {
        if ($marketingPhone === '' || $this->secret === '') {
            return false;
        }

        $waNumber = preg_replace('/[^0-9]/', '', $noHp);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }

        $message = "📢 *Lead Baru Masuk!*\n"
            . "👤 {$namaCustomer}\n"
            . "📱 {$noHp}\n";

        if ($pesan) {
            $message .= "💬 {$pesan}\n";
        }

        $message .= "\nSegera follow-up! 🚀\nhttps://wa.me/{$waNumber}";

        try {
            $response = Http::timeout(10)->post("{$this->botUrl}/send-notification", [
                'secret'  => $this->secret,
                'phone'   => $marketingPhone,
                'message' => $message,
            ]);

            if ($response->successful() && $response->json('success')) {
                return true;
            }

            Log::warning('WA notification failed', [
                'phone'    => $marketingPhone,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WA notification error: ' . $e->getMessage());
            return false;
        }
    }
}

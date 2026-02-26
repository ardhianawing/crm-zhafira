<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('app.telegram_bot_token', '');
    }

    /**
     * Kirim pesan Telegram ke chat_id tertentu
     */
    public function sendMessage(string $chatId, string $text, string $parseMode = 'HTML'): bool
    {
        if ($this->token === '' || $chatId === '') {
            return false;
        }

        try {
            $response = Http::timeout(5)->post(
                "https://api.telegram.org/bot{$this->token}/sendMessage",
                [
                    'chat_id'    => $chatId,
                    'text'       => $text,
                    'parse_mode' => $parseMode,
                ]
            );

            if ($response->successful() && $response->json('ok')) {
                return true;
            }

            Log::warning('Telegram sendMessage failed', [
                'chat_id'  => $chatId,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi lead baru ke marketing via Telegram
     */
    public function notifyNewLead(string $chatId, string $namaCustomer, string $noHp, ?string $keterangan = null): bool
    {
        $waktu = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i');

        // Buat link WA langsung
        $waNumber = preg_replace('/[^0-9]/', '', $noHp);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (!str_starts_with($waNumber, '62')) {
            $waNumber = '62' . $waNumber;
        }
        $waLink = "https://wa.me/{$waNumber}";

        $text = "📩 <b>Lead Baru dari WhatsApp</b>\n"
            . "━━━━━━━━━━━━━━━━━━\n"
            . "👤 Nama: <b>{$this->escape($namaCustomer)}</b>\n"
            . "📱 Nomor: {$this->escape($noHp)}\n"
            . "🕐 Waktu: {$waktu} WIB\n";

        if ($keterangan) {
            $text .= "📝 Pesan: {$this->escape($keterangan)}\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━━\n"
            . "👉 <a href=\"{$waLink}\">Hubungi via WhatsApp</a>\n\n"
            . "Segera follow-up! 🚀";

        return $this->sendMessage($chatId, $text);
    }

    /**
     * Escape HTML special chars untuk Telegram HTML parse mode
     */
    protected function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

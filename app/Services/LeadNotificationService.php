<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Pintu tunggal notifikasi "lead di-assign ke marketing".
 * Mengirim lewat semua kanal yang tersedia untuk si marketing:
 * - Web push (semua device yang terdaftar)
 * - Telegram (bila marketing punya telegram_chat_id)
 * - WhatsApp bot (hanya bila WA_BOT_URL dikonfigurasi — saat ini dorman by design,
 *   lihat catatan: nomor WA penerima leads tidak dipakai untuk kirim otomatis)
 *
 * Dipanggil dari webhook (auto-assign rotator) DAN dari assignment manual
 * (single/bulk/transfer) — sebelumnya assignment manual tidak mengirim apa-apa.
 */
class LeadNotificationService
{
    public function __construct(
        private PushNotificationService $pushService,
        private TelegramService $telegramService,
        private WhatsAppNotificationService $waService,
    ) {}

    /**
     * Notifikasi satu lead baru (dipakai webhook & assign satuan).
     * Mengembalikan hasil per kanal, mis. ['push' => true, 'telegram' => true, 'whatsapp' => false].
     */
    public function notifyLeadAssigned(User $marketing, Lead $lead, bool $viaWhatsApp = false): array
    {
        $pendingCount = Lead::where('assigned_to', $marketing->id)
            ->where('fase_followup', 0)
            ->count();

        $results = [
            'push' => $this->sendPush(
                $marketing,
                'Zhafira CRM',
                "Lead baru: {$lead->nama_customer} 🔔 ({$pendingCount} menunggu follow-up)"
            ),
            'telegram' => $this->sendTelegramLead($marketing, $lead),
            'whatsapp' => false,
        ];

        if ($viaWhatsApp) {
            $results['whatsapp'] = $this->sendWhatsApp($marketing, $lead);
        }

        return $results;
    }

    /**
     * Notifikasi ringkas untuk assignment massal / transfer (1 pesan per marketing,
     * bukan 1 pesan per lead).
     */
    public function notifyBulkAssigned(User $marketing, int $count, string $context = 'di-assign'): void
    {
        if ($count < 1) {
            return;
        }

        $this->sendPush(
            $marketing,
            'Zhafira CRM',
            "{$count} lead {$context} ke kamu 🔔"
        );

        if ($marketing->telegram_chat_id) {
            $this->telegramService->sendMessage(
                (string) $marketing->telegram_chat_id,
                "📦 <b>{$count} lead</b> {$context} ke kamu.\nBuka CRM untuk mulai follow-up 🚀"
            );
        }
    }

    private function sendPush(User $marketing, string $title, string $body): bool
    {
        try {
            $this->pushService->sendToUser($marketing, $title, $body, ['url' => '/marketing/tasks/today']);
            return true;
        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendTelegramLead(User $marketing, Lead $lead): bool
    {
        if (!$marketing->telegram_chat_id) {
            return false;
        }

        try {
            return $this->telegramService->notifyNewLead(
                (string) $marketing->telegram_chat_id,
                $lead->nama_customer,
                $lead->no_hp,
                $lead->keterangan
            );
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function sendWhatsApp(User $marketing, Lead $lead): bool
    {
        if (!$marketing->no_hp) {
            return false;
        }

        try {
            return $this->waService->notifyNewLead(
                $marketing->no_hp,
                $lead->nama_customer,
                $lead->no_hp,
                $lead->keterangan
            );
        } catch (\Exception $e) {
            Log::error('WA notification failed: ' . $e->getMessage());
            return false;
        }
    }
}

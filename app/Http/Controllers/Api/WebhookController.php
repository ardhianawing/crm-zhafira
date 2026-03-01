<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Lead;
use App\Models\LeadHistory;
use App\Models\User;
use App\Services\PushNotificationService;
use App\Services\TelegramService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    // Terima data satu per satu dari Sheets
    public function handleGoogleSheets(Request $request)
    {
        if ($request->secret !== config('app.webhook_secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek duplikat berdasarkan Nomor HP
        $exists = Lead::where('no_hp', $request->nomor)->exists();

        if ($exists) {
            return response()->json(['message' => 'Lead sudah ada (duplicate)', 'action' => 'skipped']);
        }

        // Auto-assign dengan rotator jika aktif
        $assignedTo = null;
        $assignedAt = null;
        $assignedName = null;

        if (AppSetting::isRotatorEnabled()) {
            $nextMarketing = AppSetting::getNextMarketingForRotation();
            if ($nextMarketing) {
                $assignedTo = $nextMarketing->id;
                $assignedAt = now();
                $assignedName = $nextMarketing->nama_lengkap;
            }
        }

        $lead = Lead::create([
            'nama_customer' => $request->nama,
            'no_hp' => $request->nomor,
            'status_prospek' => $request->status ?? 'New',
            'sumber_lead' => $request->sumber ?? 'Landing Page',
            'keterangan' => $request->keterangan,
            'assigned_to' => $assignedTo,
            'assigned_at' => $assignedAt,
        ]);

        // Kirim notifikasi ke marketing yang di-assign
        $waNotifSent = false;

        if ($assignedTo && $nextMarketing) {
            // WA notification (utama)
            if ($nextMarketing->no_hp) {
                try {
                    $waService = new WhatsAppNotificationService();
                    $waNotifSent = $waService->notifyNewLead(
                        $nextMarketing->no_hp,
                        $request->nama,
                        $request->nomor,
                        $request->keterangan
                    );
                } catch (\Exception $e) {
                    \Log::error('WA notification failed: ' . $e->getMessage());
                }
            }

            // Push notification (browser)
            try {
                $pendingCount = Lead::where('assigned_to', $nextMarketing->id)
                    ->where('fase_followup', 0)
                    ->count();
                $pushService = new PushNotificationService();
                $pushService->sendToUser(
                    $nextMarketing,
                    'Zhafira CRM',
                    "Ada Lead Masuk {$pendingCount} 🔔",
                    ['url' => '/marketing/tasks/today']
                );
            } catch (\Exception $e) {
                \Log::error('Push notification failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Lead berhasil masuk',
            'action' => 'created',
            'assigned_to' => $assignedName,
            'wa_notif_sent' => $waNotifSent,
        ]);
    }

    // Terima banyak data sekaligus dari Sheets
    public function handleBulkGoogleSheets(Request $request)
    {
        if ($request->secret !== config('app.webhook_secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $leads = $request->leads;
        $count = 0;

        foreach ($leads as $data) {
            $exists = Lead::where('no_hp', $data['nomor'])->exists();
            if (!$exists) {
                Lead::create([
                    'nama_customer' => $data['nama'],
                    'no_hp' => $data['nomor'],
                    'status_prospek' => $data['status'] ?? 'New',
                    'sumber_lead' => 'Google Sheets Bulk',
                    'keterangan' => $data['keterangan'],
                    'assigned_to' => null,
                ]);
                $count++;
            }
        }

        return response()->json(['message' => $count . ' Leads berhasil masuk ke antrean']);
    }

    // Terima data dari WhatsApp Bot
    public function handleWhatsApp(Request $request)
    {
        if ($request->secret !== config('app.webhook_secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Normalize phone number
        $phone = preg_replace('/[^0-9]/', '', $request->nomor);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        // Cek duplikat (multi-format)
        $existingLead = Lead::where('no_hp', $phone)
            ->orWhere('no_hp', '0' . substr($phone, 2))
            ->orWhere('no_hp', '+62' . substr($phone, 2))
            ->first();

        if ($existingLead) {
            $response = [
                'success' => true,
                'message' => 'Lead sudah ada (duplicate)',
                'action' => 'skipped',
                'crm_id' => $existingLead->id,
            ];

            // Return info marketing jika sudah di-assign
            if ($existingLead->assigned_to) {
                $marketing = User::find($existingLead->assigned_to);
                if ($marketing) {
                    $response['marketing'] = [
                        'id'   => $marketing->id,
                        'nama' => $marketing->nama_lengkap,
                        'wa'   => $marketing->no_hp ?? '',
                    ];
                }
            }

            return response()->json($response);
        }

        // Auto-assign dengan rotator jika aktif
        $assignedTo = null;
        $assignedAt = null;
        $nextMarketing = null;

        if (AppSetting::isRotatorEnabled()) {
            $nextMarketing = AppSetting::getNextMarketingForRotation();
            if ($nextMarketing) {
                $assignedTo = $nextMarketing->id;
                $assignedAt = now();
            }
        }

        $lead = Lead::create([
            'nama_customer' => $request->nama ?: 'Lead WhatsApp',
            'no_hp' => $phone,
            'status_prospek' => 'New',
            'sumber_lead' => $request->sumber ?: 'WhatsApp',
            'keterangan' => $request->pesan,
            'assigned_to' => $assignedTo,
            'assigned_at' => $assignedAt,
        ]);

        // Catat di lead_history
        LeadHistory::create([
            'lead_id'    => $lead->id,
            'user_id'    => null,
            'action'     => 'wa_import',
            'new_values' => [
                'nama_customer' => $request->nama,
                'no_hp'         => $phone,
                'sumber'        => 'WhatsApp Bot',
                'assigned_to'   => $nextMarketing?->nama_lengkap,
            ],
        ]);

        // Kirim notifikasi ke marketing yang di-assign
        $waNotifSent = false;
        if ($assignedTo && $nextMarketing) {
            // WA notification (utama)
            if ($nextMarketing->no_hp) {
                try {
                    $waService = new WhatsAppNotificationService();
                    $waNotifSent = $waService->notifyNewLead(
                        $nextMarketing->no_hp,
                        $request->nama,
                        $phone,
                        $request->pesan
                    );
                } catch (\Exception $e) {
                    \Log::error('WA notification failed: ' . $e->getMessage());
                }
            }

            // Push notification (browser)
            try {
                $pendingCount = Lead::where('assigned_to', $nextMarketing->id)
                    ->where('fase_followup', 0)
                    ->count();
                $pushService = new PushNotificationService();
                $pushService->sendToUser(
                    $nextMarketing,
                    'Zhafira CRM',
                    "Ada Lead Masuk {$pendingCount} 🔔",
                    ['url' => '/marketing/tasks/today']
                );
            } catch (\Exception $e) {
                \Log::error('Push notification failed: ' . $e->getMessage());
            }
        }

        // Kirim ke Google Sheets
        $sheetsUrl = config('app.google_sheets_url');
        if ($sheetsUrl) {
            try {
                Http::timeout(5)->post($sheetsUrl, [
                    'tanggal'   => now()->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                    'nama'      => $request->nama,
                    'no_hp'     => $phone,
                    'pesan'     => $request->pesan ?? '',
                    'status'    => 'New',
                    'marketing' => $nextMarketing?->nama_lengkap ?? 'Belum di-assign',
                    'sumber'    => 'WhatsApp',
                    'crm_id'    => $lead->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Google Sheets sync failed: ' . $e->getMessage());
            }
        }

        $response = [
            'success'        => true,
            'message'        => 'Lead berhasil masuk',
            'action'         => 'created',
            'crm_id'         => $lead->id,
            'wa_notif_sent'  => $waNotifSent,
        ];

        if ($nextMarketing) {
            $response['marketing'] = [
                'id'   => $nextMarketing->id,
                'nama' => $nextMarketing->nama_lengkap,
                'wa'   => $nextMarketing->no_hp ?? '',
            ];
        }

        return response()->json($response);
    }

    // Get active marketing numbers for WA Bot forwarding
    public function getMarketingNumbers(Request $request)
    {
        if ($request->secret !== config('app.webhook_secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $marketings = User::where('role', 'marketing')
            ->where('is_active', true)
            ->whereNotNull('no_hp')
            ->select('id', 'nama_lengkap', 'no_hp')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $marketings
        ]);
    }

    // Handle Telegram Bot webhook (auto-reply)
    public function handleTelegram(Request $request)
    {
        if ($request->secret !== config('app.webhook_secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $message = $request->input('message');
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');
        $firstName = $message['from']['first_name'] ?? '';

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        if ($text === '/start') {
            $reply = "Halo {$firstName}! 👋\n\n"
                . "Saya bot notifikasi <b>Zhafira CRM</b>.\n\n"
                . "Kamu akan menerima notifikasi otomatis ketika ada lead baru yang di-assign ke kamu.\n\n"
                . "📌 <b>Chat ID kamu:</b> <code>{$chatId}</code>\n"
                . "Berikan ID ini ke admin untuk diinput di CRM.";
        } elseif ($text === '/id') {
            $reply = "📌 <b>Chat ID kamu:</b> <code>{$chatId}</code>";
        } else {
            $reply = "Saya bot notifikasi otomatis.\n"
                . "Ketik /start untuk info, atau /id untuk melihat Chat ID kamu.";
        }

        $telegram = new TelegramService();
        $telegram->sendMessage((string)$chatId, $reply);

        return response()->json(['ok' => true]);
    }
}
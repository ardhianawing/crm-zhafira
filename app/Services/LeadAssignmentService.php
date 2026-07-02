<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadAssignmentService
{
    public function __construct(
        private LeadNotificationService $notificationService
    ) {}

    /**
     * Memberikan leads baru ke marketing (Assign Awal).
     * Digunakan untuk lead yang statusnya masih 'New' dan belum punya marketing.
     */
    public function assignBulk(array $leadIds, int $marketingId, int $assignedBy): int
    {
        $count = DB::transaction(function () use ($leadIds, $marketingId, $assignedBy) {
            $count = 0;
            $leads = Lead::whereIn('id', $leadIds)
                ->whereNull('assigned_to')
                ->get();

            foreach ($leads as $lead) {
                $oldValues = $lead->toArray();

                // Proses assign pertama kali
                $updated = $lead->update([
                    'assigned_to' => $marketingId,
                    'assigned_at' => now(),
                ]);

                if ($updated) {
                    // Catat riwayat
                    LeadHistory::create([
                        'lead_id' => $lead->id,
                        'user_id' => $assignedBy,
                        'action' => 'assigned',
                        'old_values' => $oldValues,
                        'new_values' => $lead->fresh()->toArray(),
                    ]);
                    $count++;
                }
            }
            return $count;
        });

        // Notifikasi setelah transaksi commit, satu ringkasan per marketing
        if ($count > 0 && ($marketing = User::find($marketingId))) {
            $this->notificationService->notifyBulkAssigned($marketing, $count, 'di-assign');
        }

        return $count;
    }

    /**
     * Memindahkan leads (Transfer) dari satu marketing ke marketing lain.
     * REVISI TERBARU: Status asli (misal: HOT) TETAP TERJAGA, tidak berubah jadi New.
     */
    public function transferBulk(array $leadIds, int $toMarketingId, int $performedBy): int
    {
        $count = DB::transaction(function () use ($leadIds, $toMarketingId, $performedBy) {
            $count = 0;
            $leads = Lead::whereIn('id', $leadIds)->get();

            foreach ($leads as $lead) {
                // Jika marketing tujuan sama dengan yang sekarang, lewati
                if ($lead->assigned_to == $toMarketingId) {
                    continue;
                }

                $oldValues = $lead->toArray();

                /**
                 * LOGIKA REVISI:
                 * Kita hanya mengubah 'assigned_to' (siapa yang pegang) 
                 * dan 'assigned_at' (kapan dipindahkan).
                 * Kolom 'status' TIDAK dimasukkan agar nilai lama (HOT/WARM) tidak hilang.
                 */
                $lead->update([
                    'assigned_to' => $toMarketingId,
                    'assigned_at' => now(),
                ]);

                // Catat sejarah sebagai 'transferred' (operan/limpahan)
                LeadHistory::create([
                    'lead_id' => $lead->id,
                    'user_id' => $performedBy,
                    'action' => 'transferred',
                    'old_values' => $oldValues,
                    'new_values' => $lead->fresh()->toArray(),
                ]);

                $count++;
            }

            return $count;
        });

        // Notifikasi ke marketing penerima operan (setelah commit)
        if ($count > 0 && ($marketing = User::find($toMarketingId))) {
            $this->notificationService->notifyBulkAssigned($marketing, $count, 'dioper');
        }

        return $count;
    }
}

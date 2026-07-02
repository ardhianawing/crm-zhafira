<?php

namespace App\Http\Controllers\Marketing;

use App\Enums\StatusProspek;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\FollowUpService;
use App\Models\LeadHistory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function __construct(
        private FollowUpService $followUpService
    ) {}

    public function todaysTasks(Request $request): View
    {
        $due = in_array($request->due, ['all', 'overdue', 'today'], true)
            ? $request->due
            : 'all';
        $status = in_array($request->status, StatusProspek::values(), true)
            ? $request->status
            : null;

        $leads = Lead::assignedTo(auth()->id())
            ->activeFollowUps()
            ->todaysTasks()
            ->when($due === 'overdue', fn ($query) => $query->whereDate('tgl_next_followup', '<', today()))
            ->when($due === 'today', fn ($query) => $query->whereDate('tgl_next_followup', today()))
            ->when($status, fn ($query, $value) => $query->where('status_prospek', $value))
            ->orderByRaw("CASE WHEN status_prospek = 'Hot' THEN 0 ELSE 1 END")
            ->orderBy('tgl_next_followup', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Semua status untuk dropdown "hasil follow-up" di tiap kartu
        $statuses = StatusProspek::cases();

        // Filter status di atas hanya menampilkan status yang mungkin ada di daftar tugas
        // (activeFollowUps mengecualikan Deal & Tidak Berminat, jadi jangan ditawarkan)
        $filterStatuses = array_values(array_filter(
            $statuses,
            fn (StatusProspek $s) => !in_array($s->value, ['Deal', 'Tidak Berminat'], true)
        ));

        return view('marketing.tasks.today', compact('leads', 'statuses', 'filterStatuses', 'due', 'status'));
    }

    public function complete(Request $request, Lead $lead)
    {
        // Authorization
        if ($lead->assigned_to !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke lead ini.');
        }

        $validated = $request->validate([
            'catatan' => 'nullable|string',
            'status_prospek' => ['required', Rule::enum(StatusProspek::class)],
            'tgl_next_followup' => 'nullable|date|after_or_equal:today',
        ], [
            'status_prospek.required' => 'Status prospek wajib dipilih.',
            'tgl_next_followup.after_or_equal' => 'Tanggal follow-up harus hari ini atau setelahnya.',
        ]);

        $this->followUpService->completeFollowUp($lead, $validated, auth()->id());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Follow-up untuk {$lead->nama_customer} berhasil diselesaikan.",
            ]);
        }

        return back()->with('success', "Follow-up untuk {$lead->nama_customer} berhasil diselesaikan.");
    }

    public function quickReschedule(Request $request, Lead $lead)
    {
        if ($lead->assigned_to !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'tgl_next_followup' => 'required|date|after_or_equal:today',
        ], [
            'tgl_next_followup.after_or_equal' => 'Tanggal harus hari ini atau setelahnya.',
        ]);

        $oldDate = $lead->tgl_next_followup?->format('Y-m-d');
        $lead->update(['tgl_next_followup' => $validated['tgl_next_followup']]);

        LeadHistory::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'action' => 'rescheduled',
            'old_values' => ['tgl_next_followup' => $oldDate],
            'new_values' => ['tgl_next_followup' => $validated['tgl_next_followup']],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal follow-up {$lead->nama_customer} diubah ke " . Carbon::parse($validated['tgl_next_followup'])->format('d/m/Y'),
            ]);
        }

        return back()->with('success', 'Jadwal follow-up berhasil diubah.');
    }
}

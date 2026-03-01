<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Enums\StatusProspek;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 1000);
        
        $leads = Lead::with('assignedUser')
            ->when($request->search, function($query, $search) {
                return $query->where('nama_customer', 'like', "%{$search}%")
                             ->orWhere('no_hp', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'search' => $request->search]);

        return view('admin.leads.index', compact('leads', 'perPage'));
    }

    public function create()
    {
        $statuses = StatusProspek::cases();
        $marketingUsers = User::marketing()->active()->get();
        return view('admin.leads.create', compact('statuses', 'marketingUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_customer' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'status_prospek' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'fase_followup' => 'nullable|integer',
            'tgl_next_followup' => 'nullable|date',
            'catatan_terakhir' => 'nullable|string',
        ]);

        if (!empty($validated['assigned_to'])) {
            $validated['assigned_at'] = now();
        }

        $lead = Lead::create($validated);

        // Record history
        $lead->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'new_values' => $lead->toArray(),
        ]);

        return redirect()->route('admin.leads.index')->with('success', 'Lead berhasil ditambahkan.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['assignedUser', 'histories.user']);
        return view('admin.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $statuses = StatusProspek::cases();
        $marketingUsers = User::marketing()->active()->get();
        return view('admin.leads.edit', compact('lead', 'statuses', 'marketingUsers'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'nama_customer' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'status_prospek' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'fase_followup' => 'nullable|integer',
            'tgl_next_followup' => 'nullable|date',
            'catatan_terakhir' => 'nullable|string',
        ]);

        $oldValues = $lead->toArray();
        
        if (isset($validated['assigned_to']) && $validated['assigned_to'] != $lead->assigned_to) {
            $validated['assigned_at'] = now();
        }

        $lead->update($validated);

        // Record history
        $lead->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $lead->fresh()->toArray(),
        ]);

        return redirect()->route('admin.leads.index')->with('success', 'Lead berhasil diupdate.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('admin.leads.index')->with('success', 'Lead berhasil dihapus.');
    }

    public function history(Lead $lead)
    {
        $lead->load(['histories.user']);
        return view('admin.leads.history', compact('lead'));
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate(['status' => 'required|string']);
        $oldStatus = $lead->status_prospek->value ?? $lead->status_prospek;
        $lead->update(['status_prospek' => $request->status]);

        $lead->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'old_values' => ['status_prospek' => $oldStatus],
            'new_values' => ['status_prospek' => $request->status],
        ]);

        return back()->with('success', 'Status lead berhasil diupdate.');
    }

    public function bulkUpload()
    {
        return view('admin.leads.bulk-upload');
    }

    public function bulkUploadProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $rows = Excel::toArray(null, $request->file('file'))[0];

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau format tidak valid.');
        }

        // Detect headers
        $headers = array_map(fn($h) => strtolower(trim($h ?? '')), $rows[0]);
        $namaCol = array_search('nama', $headers);
        $hpCol = array_search('no_hp', $headers);
        $statusCol = array_search('status', $headers);
        $ketCol = array_search('keterangan', $headers);

        if ($namaCol === false || $hpCol === false) {
            return back()->with('error', 'Kolom "nama" dan "no_hp" wajib ada di file.');
        }

        $dataRows = array_slice($rows, 1); // skip header

        if (count($dataRows) > 500) {
            return back()->with('error', 'Maksimal 500 baris per upload. File ini memiliki ' . count($dataRows) . ' baris.');
        }

        $success = 0;
        $duplicates = 0;
        $failed = 0;
        $failedRows = [];

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2; // 1-based + header
            $nama = trim($row[$namaCol] ?? '');
            $noHp = trim((string) ($row[$hpCol] ?? ''));
            $status = ($statusCol !== false) ? (trim($row[$statusCol] ?? '') ?: 'New') : 'New';
            $keterangan = ($ketCol !== false) ? (trim($row[$ketCol] ?? '') ?: null) : null;

            // Validasi
            if (empty($nama) || empty($noHp)) {
                $failed++;
                $failedRows[] = ['row' => $rowNum, 'nama' => $nama, 'no_hp' => $noHp, 'reason' => 'Nama atau No HP kosong'];
                continue;
            }

            if (mb_strlen($nama) > 100) {
                $failed++;
                $failedRows[] = ['row' => $rowNum, 'nama' => $nama, 'no_hp' => $noHp, 'reason' => 'Nama melebihi 100 karakter'];
                continue;
            }

            if (strlen($noHp) > 20) {
                $failed++;
                $failedRows[] = ['row' => $rowNum, 'nama' => $nama, 'no_hp' => $noHp, 'reason' => 'No HP melebihi 20 karakter'];
                continue;
            }

            // Validate status
            $validStatuses = StatusProspek::values();
            if (!in_array($status, $validStatuses)) {
                $status = 'New';
            }

            // Normalisasi no HP
            $phone = preg_replace('/[^0-9]/', '', $noHp);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (!str_starts_with($phone, '62')) {
                $phone = '62' . $phone;
            }

            // Cek duplikat (multi-format, sama seperti webhook)
            $exists = Lead::where('no_hp', $phone)
                ->orWhere('no_hp', '0' . substr($phone, 2))
                ->orWhere('no_hp', '+62' . substr($phone, 2))
                ->orWhere('no_hp', $noHp)
                ->exists();

            if ($exists) {
                $duplicates++;
                continue;
            }

            $lead = Lead::create([
                'nama_customer' => $nama,
                'no_hp' => $phone,
                'status_prospek' => $status,
                'sumber_lead' => 'Bulk Upload',
                'keterangan' => $keterangan,
            ]);

            $lead->histories()->create([
                'user_id' => auth()->id(),
                'action' => 'bulk_import',
                'new_values' => $lead->toArray(),
            ]);

            $success++;
        }

        return back()->with('bulk_result', [
            'success' => $success,
            'duplicates' => $duplicates,
            'failed' => $failed,
            'failed_rows' => $failedRows,
            'total' => count($dataRows),
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_bulk_upload_leads.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['nama', 'no_hp', 'status', 'keterangan']);
            fputcsv($handle, ['John Doe', '081234567890', 'New', 'Dari pameran']);
            fputcsv($handle, ['Jane Smith', '6289876543210', 'Warm', 'Referensi teman']);
            fclose($handle);
        }, 200, $headers);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadAssignmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 1000);
        $filters = $request->validate([
            'status_filter' => ['nullable', Rule::in(['New', 'Cold', 'Warm', 'Hot', 'Deal'])],
            'source_filter' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $marketingUsers = User::where('role', 'marketing')
            ->where('is_active', true)
            ->withCount('leads')
            ->get();

        $unassignedLeads = $this->filteredUnassignedQuery($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'unassigned_page')
            ->withQueryString();

        $leadSources = Lead::query()
            ->whereNotNull('sumber_lead')
            ->where('sumber_lead', '!=', '')
            ->distinct()
            ->orderBy('sumber_lead')
            ->pluck('sumber_lead');

        $assignedLeads = Lead::whereNotNull('assigned_to')
            ->with('assignedUser')
            ->when($request->marketing_filter, function ($query) use ($request) {
                return $query->where('assigned_to', $request->marketing_filter);
            })
            ->orderByRaw("CASE WHEN status_prospek = 'Hot' THEN 0 ELSE 1 END")
            ->orderBy('assigned_at', 'desc')
            ->paginate($perPage, ['*'], 'assigned_page')
            ->withQueryString();

        $rotatorEnabled = AppSetting::isRotatorEnabled();

        return view('admin.assignment.index', compact(
            'marketingUsers',
            'unassignedLeads',
            'assignedLeads',
            'leadSources',
            'perPage',
            'rotatorEnabled'
        ));
    }

    public function assignSingle(Request $request, Lead $lead)
    {
        $request->validate([
            'marketing_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'marketing')
                    ->where('is_active', true)),
            ],
        ]);

        $lead->update([
            'assigned_to' => $request->marketing_id,
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'Lead berhasil di-assign.');
    }

    public function assignBulk(Request $request, LeadAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'lead_ids' => ['required_unless:select_all_filtered,1', 'array'],
            'lead_ids.*' => ['integer', 'exists:leads,id'],
            'select_all_filtered' => ['nullable', 'boolean'],
            'status_filter' => ['nullable', Rule::in(['New', 'Cold', 'Warm', 'Hot', 'Deal'])],
            'source_filter' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
            'marketing_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'marketing')
                    ->where('is_active', true)),
            ],
        ]);

        $leadIds = $request->boolean('select_all_filtered')
            ? $this->filteredUnassignedQuery($validated)->pluck('id')->all()
            : $validated['lead_ids'];

        $assignedCount = $assignmentService->assignBulk(
            $leadIds,
            (int) $validated['marketing_id'],
            (int) auth()->id()
        );

        return back()->with('success', $assignedCount . ' lead berhasil di-assign.');
    }

    public function transferBulk(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer|exists:leads,id',
            'marketing_id' => 'required|exists:users,id',
        ]);

        Lead::whereIn('id', $request->lead_ids)
            ->update([
                'assigned_to' => $request->marketing_id,
                'assigned_at' => now(),
            ]);

        return back()->with('success', count($request->lead_ids) . ' lead berhasil ditransfer.');
    }

    public function deleteBulk(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer|exists:leads,id',
        ]);

        Lead::whereIn('id', $request->lead_ids)->delete();

        return back()->with('success', count($request->lead_ids) . ' lead berhasil dihapus.');
    }

    public function toggleRotator()
    {
        $current = AppSetting::isRotatorEnabled();
        AppSetting::setValue('lead_rotator_enabled', $current ? 'false' : 'true');

        return back()->with('success', 'Lead rotator ' . ($current ? 'dinonaktifkan' : 'diaktifkan') . '.');
    }

    private function filteredUnassignedQuery(array $filters): Builder
    {
        return Lead::query()
            ->whereNull('assigned_to')
            ->when($filters['status_filter'] ?? null, function (Builder $query, string $status) {
                $query->where('status_prospek', $status);
            })
            ->when($filters['source_filter'] ?? null, function (Builder $query, string $source) {
                $query->where('sumber_lead', $source);
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('nama_customer', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%");
                });
            });
    }
}

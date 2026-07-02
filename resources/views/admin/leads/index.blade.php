@extends('layouts.app')

@section('title', 'Database Leads - Zhafira CRM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-people-fill" style="color: #0f3d2e;"></i> Database Leads</h4>
        <p class="text-muted mb-0">Total database: <b>{{ $leads->total() }}</b> baris data.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.leads.bulk-upload') }}" class="btn btn-outline-success">
            <i class="bi bi-cloud-arrow-up"></i> Bulk Upload
        </a>
        <a href="{{ route('admin.leads.create') }}" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
            <i class="bi bi-plus-circle"></i> Tambah Lead
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.leads.index') }}" method="GET">
            <input type="hidden" name="duplicates" value="0">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label for="filterStatus" class="form-label small fw-bold mb-1">Status</label>
                    <select id="filterStatus" name="status_filter" class="form-select form-select-sm">
                        <option value="">Semua status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status_filter') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="filterSource" class="form-label small fw-bold mb-1">Sumber</label>
                    <select id="filterSource" name="source_filter" class="form-select form-select-sm">
                        <option value="">Semua sumber</option>
                        @foreach($leadSources as $source)
                            <option value="{{ $source }}" {{ request('source_filter') === $source ? 'selected' : '' }}>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="filterMarketing" class="form-label small fw-bold mb-1">Marketing</label>
                    <select id="filterMarketing" name="marketing_filter" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="unassigned" {{ request('marketing_filter') === 'unassigned' ? 'selected' : '' }}>
                            Belum di-assign
                        </option>
                        @foreach($marketingUsers as $user)
                            <option value="{{ $user->id }}" {{ request('marketing_filter') == $user->id ? 'selected' : '' }}>
                                {{ $user->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterSearch" class="form-label small fw-bold mb-1">Cari lead</label>
                    <input id="filterSearch" type="search" name="search" class="form-control form-control-sm"
                        placeholder="Nama atau nomor HP" value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-1 flex-wrap">
                    <button type="submit" class="btn btn-sm btn-dark">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('admin.leads.index', ['per_page' => $perPage]) }}"
                        class="btn btn-sm btn-outline-secondary" title="Reset filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <label class="btn btn-sm {{ request()->boolean('duplicates') ? 'btn-warning' : 'btn-outline-warning' }}">
                        <input class="visually-hidden" type="checkbox" name="duplicates" value="1"
                               onchange="this.form.submit()" {{ request()->boolean('duplicates') ? 'checked' : '' }}>
                        <i class="bi bi-files"></i> Duplikat
                    </label>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 pt-2 border-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold text-nowrap">Tampilkan:</span>
                    <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        @foreach([50, 100, 500, 1000] as $value)
                            <option value="{{ $value }}" {{ $perPage == $value ? 'selected' : '' }}>{{ $value }} Baris</option>
                        @endforeach
                    </select>
                </div>
                <span class="text-muted small">Menampilkan {{ $leads->firstItem() ?? 0 }} - {{ $leads->lastItem() ?? 0 }} dari {{ $leads->total() }} data</span>
            </div>
        </form>
    </div>
</div>

{{-- Form hapus massal sengaja di luar tabel (checkbox terhubung via atribut form="bulkDeleteForm")
     agar tidak menjadi nested form dengan form hapus per-baris di dalam tabel --}}
<form action="{{ route('admin.leads.bulk-delete') }}" method="POST" id="bulkDeleteForm"
    onsubmit="return confirm('Hapus ' + (document.querySelectorAll('.lead-checkbox:checked').length) + ' lead terpilih? Tindakan ini tidak bisa dibatalkan.');">
    @csrf @method('DELETE')
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="p-2 px-3 bg-light border-bottom d-flex align-items-center gap-2 flex-wrap" id="bulkActionBar">
        <span class="small text-muted" id="bulkSelectedCount">Belum ada lead dipilih.</span>
        <button type="submit" form="bulkDeleteForm" class="btn btn-sm btn-danger ms-auto" id="bulkDeleteButton" disabled>
            <i class="bi bi-trash"></i> Hapus Terpilih
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr style="background-color: #0f3d2e; color: #fff;">
                    <th style="width: 38px; background-color: #0f3d2e; color: #fff;">
                        <input class="form-check-input" type="checkbox" id="selectAllLeads" title="Pilih semua di halaman ini">
                    </th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Customer</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">WhatsApp</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Sumber</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Marketing</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Status</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td>
                        <input class="form-check-input lead-checkbox" type="checkbox"
                            name="lead_ids[]" value="{{ $lead->id }}" form="bulkDeleteForm">
                    </td>
                    <td>
                        <div class="fw-bold">{{ $lead->nama_customer }}</div>
                        <small class="text-muted">{{ $lead->created_at->format('d M Y') }}</small>
                    </td>
                    <td>
                        <div>{{ $lead->no_hp }}</div>
                        @if($lead->duplicate_matches_count > 1)
                            <span class="badge bg-warning text-dark mt-1">
                                <i class="bi bi-files"></i> Duplikat {{ $lead->duplicate_matches_count }}
                            </span>
                        @endif
                    </td>
                    <td><span class="badge" style="background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6;">{{ $lead->sumber_lead ?? '-' }}</span></td>
                    <td>
                        @if($lead->assignedUser)
                            <span class="badge" style="background-color: rgba(13,202,240,0.1); color: #0dcaf0; border: 1px solid rgba(13,202,240,0.2);">{{ $lead->assignedUser->nama_lengkap }}</span>
                        @else
                            <span class="badge" style="background-color: rgba(220,53,69,0.1); color: #dc3545;">Belum Di-assign</span>
                        @endif
                    </td>
                    <td>
                        <x-badge-status :status="$lead->status_prospek" />
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="{{ route('admin.leads.edit', $lead->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil text-primary"></i></a>
                            <form action="{{ route('admin.leads.destroy', $lead->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data ini?')"><i class="bi bi-trash text-danger"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5">Tidak ada data leads.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $leads->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllLeads');
        const checkboxes = Array.from(document.querySelectorAll('.lead-checkbox'));
        const deleteButton = document.getElementById('bulkDeleteButton');
        const selectedCount = document.getElementById('bulkSelectedCount');

        function refresh() {
            const checked = checkboxes.filter(cb => cb.checked).length;
            deleteButton.disabled = checked === 0;
            selectedCount.textContent = checked === 0
                ? 'Belum ada lead dipilih.'
                : `${checked} lead dipilih.`;
            if (selectAll) {
                selectAll.checked = checked > 0 && checked === checkboxes.length;
                selectAll.indeterminate = checked > 0 && checked < checkboxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                refresh();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', refresh));
        refresh();
    });
</script>
@endpush

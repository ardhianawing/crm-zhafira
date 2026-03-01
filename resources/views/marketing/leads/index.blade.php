@extends('layouts.app')

@section('title', 'Leads Saya - Zhafira CRM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-people" style="color: #0f3d2e;"></i> Leads Saya
    </h4>
    <a href="{{ route('marketing.leads.create') }}" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
        <i class="bi bi-plus-circle"></i> Tambah Lead
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama/no HP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('marketing.leads.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Leads List (Card View for Mobile) -->
<div class="d-md-none">
    @forelse($leads as $lead)
        <x-lead-card :lead="$lead" />
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
        <p>Belum ada lead</p>
        <a href="{{ route('marketing.leads.create') }}" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
            <i class="bi bi-plus-circle"></i> Tambah Lead Pertama
        </a>
    </div>
    @endforelse
</div>

<!-- Leads Table (Desktop) -->
<div class="card d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background-color: #0f3d2e; color: #fff;">
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Nama Customer</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">No HP</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Status</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Sumber</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Fase</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Next Follow-up</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr id="lead-row-{{ $lead->id }}" class="{{ $lead->isOverdue() ? 'table-danger' : ($lead->isDueToday() ? 'table-warning' : '') }}">
                        <td>
                            <strong>{{ $lead->nama_customer }}</strong>
                            @if($lead->isOverdue())
                                <i class="bi bi-exclamation-triangle-fill text-danger" title="Overdue"></i>
                            @elseif($lead->isDueToday())
                                <span class="badge bg-warning text-dark">Hari ini</span>
                            @endif
                        </td>
                        <td>{{ $lead->no_hp }}</td>
                        <td>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm dropdown-toggle p-0 border-0" type="button" data-bs-toggle="dropdown" id="status-btn-{{ $lead->id }}">
                                    <x-badge-status :status="$lead->status_prospek" />
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach($statuses as $status)
                                    <li>
                                        <a class="dropdown-item quick-status-item {{ $lead->status_prospek->value == $status->value ? 'active' : '' }}"
                                           href="#"
                                           data-lead-id="{{ $lead->id }}"
                                           data-status="{{ $status->value }}">
                                            {{ $status->value }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                        <td>
                            @if($lead->sumber_lead)
                                <small class="text-muted">{{ $lead->sumber_lead }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">Fase {{ $lead->fase_followup }}</span></td>
                        <td>
                            @if($lead->tgl_next_followup)
                                {{ $lead->tgl_next_followup->format('d/m/Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <x-whatsapp-dropdown :phone="$lead->no_hp" :lead="$lead" class="btn-sm" label="WA" />
                                <a href="{{ route('marketing.leads.show', $lead) }}" class="btn btn-sm" style="border-color: #0f3d2e; color: #0f3d2e;">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Belum ada lead
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($leads->hasPages())
    <div class="card-footer">
        {{ $leads->links() }}
    </div>
    @endif
</div>

<!-- Mobile Pagination -->
<div class="d-md-none mt-3">
    {{ $leads->links() }}
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="quickStatusToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusColors = {
        'New': { bg: '#6c757d', color: '#fff' },
        'Cold': { bg: '#0dcaf0', color: '#000' },
        'Warm': { bg: '#ffc107', color: '#000' },
        'Hot': { bg: '#dc3545', color: '#fff' },
        'Deal': { bg: '#198754', color: '#fff' },
    };

    // Handle both table (.quick-status-item) and card (.card-quick-status) dropdowns
    document.querySelectorAll('.quick-status-item, .card-quick-status').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const leadId = this.dataset.leadId;
            const newStatus = this.dataset.status;

            fetch(`/marketing/leads/${leadId}/quick-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status_prospek: newStatus }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update badge in table
                    const tableBtn = document.getElementById('status-btn-' + leadId);
                    if (tableBtn) {
                        const badge = tableBtn.querySelector('.badge');
                        badge.textContent = newStatus;
                        badge.style.backgroundColor = statusColors[newStatus].bg;
                        badge.style.color = statusColors[newStatus].color;
                        tableBtn.closest('.dropdown').querySelectorAll('.dropdown-item').forEach(di => {
                            di.classList.remove('active');
                            if (di.dataset.status === newStatus) di.classList.add('active');
                        });
                    }

                    // Update badge in card
                    const cardBtn = document.getElementById('card-status-btn-' + leadId);
                    if (cardBtn) {
                        const badge = cardBtn.querySelector('.badge');
                        badge.textContent = newStatus;
                        badge.style.backgroundColor = statusColors[newStatus].bg;
                        badge.style.color = statusColors[newStatus].color;
                        cardBtn.closest('.dropdown').querySelectorAll('.dropdown-item').forEach(di => {
                            di.classList.remove('active');
                            if (di.dataset.status === newStatus) di.classList.add('active');
                        });
                    }

                    showToast(data.message);
                }
            })
            .catch(() => showToast('Gagal mengubah status', true));
        });
    });

    function showToast(message, isError) {
        const toast = document.getElementById('quickStatusToast');
        const toastBody = document.getElementById('toastMessage');
        toast.className = 'toast align-items-center text-white border-0 ' + (isError ? 'bg-danger' : 'bg-success');
        toastBody.textContent = message;
        new bootstrap.Toast(toast).show();
    }
});
</script>
@endpush

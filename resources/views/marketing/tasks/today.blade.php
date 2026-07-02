@extends('layouts.app')

@section('title', 'Tugas Hari Ini - Zhafira CRM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-calendar-check" style="color: #0f3d2e;"></i> Tugas Hari Ini
    </h5>
    <span class="badge" style="background-color: #0f3d2e; color: #fff; font-size: 0.8rem;" id="taskCount">{{ $leads->total() }} tugas</span>
</div>

<form method="GET" class="card card-body py-2 px-3 mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-6 col-md-4">
            <select name="due" class="form-select form-select-sm">
                <option value="all" {{ $due === 'all' ? 'selected' : '' }}>Semua Jatuh Tempo</option>
                <option value="overdue" {{ $due === 'overdue' ? 'selected' : '' }}>Overdue</option>
                <option value="today" {{ $due === 'today' ? 'selected' : '' }}>Hari Ini</option>
            </select>
        </div>
        <div class="col-6 col-md-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                @foreach($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}" {{ $status === $statusOption->value ? 'selected' : '' }}>
                        {{ $statusOption->value }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 d-flex gap-2">
            <button class="btn btn-sm btn-zhafira flex-fill" type="submit">
                <i class="bi bi-funnel"></i> Terapkan
            </button>
            <a href="{{ route('marketing.tasks.today') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

@if($leads->count() > 0)
<div class="row">
    @foreach($leads as $lead)
    <div class="col-md-6 mb-2" id="task-card-{{ $lead->id }}">
        <div class="card {{ $lead->isOverdue() ? 'border-danger' : '' }}">
            <div class="card-body py-2 px-3">
                {{-- Header: Nama + Status --}}
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong style="font-size: 0.9rem;">{{ $lead->nama_customer }}</strong>
                    <x-badge-status :status="$lead->status_prospek" />
                </div>

                {{-- Info ringkas --}}
                <div class="d-flex align-items-center text-muted flex-wrap" style="font-size: 0.73rem; gap: 0.4rem; line-height: 1.6;">
                    <span><i class="bi bi-phone"></i> {{ $lead->no_hp }}</span>
                    <span>·</span>
                    <span>F{{ $lead->fase_followup }}</span>
                    @if($lead->sumber_lead)
                        <span>·</span>
                        <span>{{ $lead->sumber_lead }}</span>
                    @endif
                    <span>·</span>
                    @if($lead->isOverdue())
                        <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> {{ $lead->tgl_next_followup->diffForHumans() }}</span>
                    @else
                        <span><i class="bi bi-calendar"></i> {{ $lead->tgl_next_followup->format('d/m') }}</span>
                    @endif
                </div>

                @if($lead->catatan_terakhir)
                <div style="font-size: 0.73rem;" class="text-muted mt-1">{{ Str::limit($lead->catatan_terakhir, 60) }}</div>
                @endif

                {{-- Actions: Chat + Detail --}}
                <div class="d-flex gap-1 mt-2">
                    <x-whatsapp-dropdown :phone="$lead->no_hp" :lead="$lead" class="btn-sm flex-fill" />
                    <a href="{{ route('marketing.leads.show', $lead) }}" class="btn btn-sm flex-fill py-1" style="border-color: #0f3d2e; color: #0f3d2e; font-size: 0.75rem;">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                </div>

                {{-- Alur utama: hasil + status + selesai --}}
                <div class="border-top mt-2 pt-2">
                    <div class="mb-2">
                        <div class="small fw-semibold mb-1">Hasil cepat</div>
                        <div class="quick-result-scroll d-flex gap-1 overflow-auto pb-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-result-btn"
                                    data-lead-id="{{ $lead->id }}" data-status="Tidak Respon"
                                    data-note="Belum merespons WhatsApp">Tidak Respon</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-result-btn"
                                    data-lead-id="{{ $lead->id }}" data-status="Warm"
                                    data-note="Customer tertarik dan meminta informasi lanjutan">Tertarik</button>
                            <button type="button" class="btn btn-sm btn-outline-warning quick-result-btn"
                                    data-lead-id="{{ $lead->id }}" data-status="Warm"
                                    data-note="Customer meminta informasi harga">Minta Harga</button>
                            <button type="button" class="btn btn-sm btn-outline-danger quick-result-btn"
                                    data-lead-id="{{ $lead->id }}" data-status="Hot"
                                    data-note="Customer ingin menjadwalkan survei">Jadwal Survei</button>
                            <button type="button" class="btn btn-sm btn-outline-success quick-result-btn"
                                    data-lead-id="{{ $lead->id }}" data-status="Deal"
                                    data-note="Customer deal">Deal</button>
                            <button type="button" class="btn btn-sm btn-outline-dark quick-result-btn"
                                    data-lead-id="{{ $lead->id }}" data-status="Tidak Berminat"
                                    data-note="Customer menyatakan tidak berminat">Tidak Berminat</button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <textarea class="form-control form-control-sm" rows="2"
                                  placeholder="Catat hasil follow-up..."
                                  id="catatan-input-{{ $lead->id }}"
                                  style="font-size: 0.75rem;"></textarea>
                    </div>

                    <div class="d-flex gap-2 mb-2">
                        <select class="form-select form-select-sm task-status-select"
                                id="status-input-{{ $lead->id }}"
                                style="font-size: 0.75rem;">
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}" {{ $lead->status_prospek->value === $statusOption->value ? 'selected' : '' }}>
                                    {{ $statusOption->value }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button"
                                class="btn btn-sm complete-followup-btn text-nowrap"
                                style="background-color: #0f3d2e; color: #fff; font-size: 0.75rem;"
                                data-lead-id="{{ $lead->id }}">
                            <i class="bi bi-check-circle"></i> Selesaikan Follow-up
                        </button>
                    </div>

                    @if($lead->fase_followup >= 3)
                    <div class="mb-2">
                        <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-info-circle"></i> Fase 3 — pilih tanggal next:</small>
                        <input type="date" class="form-control form-control-sm mt-1" id="next-followup-{{ $lead->id }}" min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDays(7)->format('Y-m-d') }}" style="font-size: 0.75rem;">
                    </div>
                    @endif

                    <div class="text-end">
                        <a class="small text-muted text-decoration-none"
                           data-bs-toggle="collapse"
                           href="#reschedule-{{ $lead->id }}">
                            <i class="bi bi-calendar-event"></i> Atur ulang jadwal
                        </a>
                    </div>
                    <div class="collapse mt-2" id="reschedule-{{ $lead->id }}">
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control"
                                   id="reschedule-date-{{ $lead->id }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   value="{{ now()->addDay()->format('Y-m-d') }}">
                            <button class="btn btn-outline-secondary reschedule-btn"
                                    type="button"
                                    data-lead-id="{{ $lead->id }}">
                                Simpan Jadwal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@if($leads->hasPages())
<div class="mt-3">
    {{ $leads->links() }}
</div>
@endif
@else
<div class="card">
    <div class="card-body text-center py-4">
        <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
        <h6>Tidak ada tugas follow-up hari ini</h6>
        <p class="text-muted small">Semua follow-up sudah dikerjakan.</p>
        <a href="{{ route('marketing.leads.index') }}" class="btn btn-sm" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
            <i class="bi bi-people"></i> Lihat Semua Leads
        </a>
    </div>
</div>
@endif

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="taskToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="taskToastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .quick-result-scroll {
        scrollbar-width: none;
    }
    .quick-result-scroll::-webkit-scrollbar {
        display: none;
    }
    .quick-result-btn {
        min-height: 38px;
        white-space: nowrap;
    }
    @media (max-width: 767.98px) {
        main.py-4 {
            padding-top: 0.75rem !important;
        }
        main .container-fluid {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .quick-result-btn,
        .complete-followup-btn,
        .task-status-select {
            min-height: 44px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let remainingTaskCount = {{ $leads->total() }};

    function showToast(message, isError) {
        const toast = document.getElementById('taskToast');
        const body = document.getElementById('taskToastMessage');
        toast.className = 'toast align-items-center text-white border-0 ' + (isError ? 'bg-danger' : 'bg-success');
        body.textContent = message;
        new bootstrap.Toast(toast).show();
    }

    function updateTaskCount() {
        remainingTaskCount = Math.max(0, remainingTaskCount - 1);
        document.getElementById('taskCount').textContent = remainingTaskCount + ' tugas';
    }

    document.querySelectorAll('.quick-result-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            document.getElementById('status-input-' + leadId).value = this.dataset.status;
            document.getElementById('catatan-input-' + leadId).value = this.dataset.note;

            this.closest('.quick-result-scroll').querySelectorAll('.quick-result-btn').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Selesai Follow-up: complete follow-up + naikkan fase
    document.querySelectorAll('.complete-followup-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            const card = document.getElementById('task-card-' + leadId);
            const status = document.getElementById('status-input-' + leadId).value;
            const catatan = document.getElementById('catatan-input-' + leadId)?.value || '';
            const nextFollowup = document.getElementById('next-followup-' + leadId)?.value || '';

            const body = { status_prospek: status, catatan: catatan };
            if (nextFollowup) body.tgl_next_followup = nextFollowup;

            this.disabled = true;
            const self = this;

            fetch(`/marketing/tasks/${leadId}/complete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    card.style.transition = 'opacity 0.4s ease';
                    card.style.opacity = '0';
                    setTimeout(() => { card.classList.add('d-none'); updateTaskCount(); }, 400);
                    showToast(data.message);
                } else {
                    self.disabled = false;
                    showToast('Gagal menyelesaikan follow-up', true);
                }
            })
            .catch(() => { self.disabled = false; showToast('Gagal menyelesaikan follow-up', true); });
        });
    });

    document.querySelectorAll('.reschedule-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            const newDate = document.getElementById('reschedule-date-' + leadId).value;
            if (!newDate) { showToast('Pilih tanggal', true); return; }

            this.disabled = true;
            const self = this;

            fetch(`/marketing/leads/${leadId}/quick-reschedule`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ tgl_next_followup: newDate }),
            })
            .then(r => r.json())
            .then(data => {
                self.disabled = false;
                if (data.success) {
                    const card = document.getElementById('task-card-' + leadId);
                    card.style.transition = 'opacity 0.4s ease';
                    card.style.opacity = '0';
                    setTimeout(() => { card.classList.add('d-none'); updateTaskCount(); }, 400);
                    showToast(data.message);
                } else {
                    showToast('Gagal mengubah jadwal', true);
                }
            })
            .catch(() => { self.disabled = false; showToast('Gagal mengubah jadwal', true); });
        });
    });
});
</script>
@endpush

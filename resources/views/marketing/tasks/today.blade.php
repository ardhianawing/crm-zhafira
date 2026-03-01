@extends('layouts.app')

@section('title', 'Tugas Hari Ini - Zhafira CRM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-calendar-check" style="color: #0f3d2e;"></i> Tugas Hari Ini
    </h5>
    <span class="badge" style="background-color: #0f3d2e; color: #fff; font-size: 0.8rem;" id="taskCount">{{ $leads->count() }} tugas</span>
</div>

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

                {{-- Quick Status (1-tap complete) --}}
                <div class="border-top mt-2 pt-2">
                    <div class="d-flex gap-1 mb-1">
                        @php
                            $statusColors = ['New'=>'#6c757d','Cold'=>'#0dcaf0','Warm'=>'#ffc107','Hot'=>'#dc3545','Deal'=>'#198754'];
                            $darkText = ['Cold','Warm'];
                        @endphp
                        @foreach($statuses as $status)
                        <button type="button"
                                class="btn flex-fill quick-complete-btn"
                                style="
                                    font-size: 0.7rem;
                                    padding: 0.2rem 0;
                                    background-color: {{ $lead->status_prospek->value == $status->value ? $statusColors[$status->value] : 'transparent' }};
                                    color: {{ $lead->status_prospek->value == $status->value ? (in_array($status->value, $darkText) ? '#000' : '#fff') : $statusColors[$status->value] }};
                                    border: 1px solid {{ $statusColors[$status->value] }};
                                "
                                data-lead-id="{{ $lead->id }}"
                                data-status="{{ $status->value }}">
                            {{ $status->value }}
                        </button>
                        @endforeach
                    </div>

                    {{-- Catatan + Reschedule inline --}}
                    <div class="d-flex gap-3" style="font-size: 0.73rem;">
                        <a class="text-muted text-decoration-none" data-bs-toggle="collapse" href="#catatan-{{ $lead->id }}">
                            <i class="bi bi-chat-left-text"></i> Catatan
                        </a>
                        <a class="text-muted text-decoration-none" data-bs-toggle="collapse" href="#reschedule-{{ $lead->id }}">
                            <i class="bi bi-calendar-event"></i> Reschedule
                        </a>
                    </div>
                    <div class="collapse mt-1" id="catatan-{{ $lead->id }}">
                        <textarea class="form-control form-control-sm" rows="2" placeholder="Hasil follow-up..." id="catatan-input-{{ $lead->id }}" style="font-size: 0.75rem;"></textarea>
                    </div>
                    <div class="collapse mt-1" id="reschedule-{{ $lead->id }}">
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="reschedule-date-{{ $lead->id }}" min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDays(1)->format('Y-m-d') }}" style="font-size: 0.75rem;">
                            <button class="btn btn-outline-primary reschedule-btn" type="button" data-lead-id="{{ $lead->id }}" style="font-size: 0.75rem;">
                                <i class="bi bi-check"></i>
                            </button>
                        </div>
                    </div>

                    @if($lead->fase_followup >= 3)
                    <div class="mt-1">
                        <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-info-circle"></i> Fase 3 — pilih tanggal next:</small>
                        <input type="date" class="form-control form-control-sm mt-1" id="next-followup-{{ $lead->id }}" min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDays(7)->format('Y-m-d') }}" style="font-size: 0.75rem;">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(message, isError) {
        const toast = document.getElementById('taskToast');
        const body = document.getElementById('taskToastMessage');
        toast.className = 'toast align-items-center text-white border-0 ' + (isError ? 'bg-danger' : 'bg-success');
        body.textContent = message;
        new bootstrap.Toast(toast).show();
    }

    function updateTaskCount() {
        const remaining = document.querySelectorAll('[id^="task-card-"]:not(.d-none)').length;
        document.getElementById('taskCount').textContent = remaining + ' tugas';
    }

    document.querySelectorAll('.quick-complete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            const status = this.dataset.status;
            const catatan = document.getElementById('catatan-input-' + leadId)?.value || '';
            const nextFollowup = document.getElementById('next-followup-' + leadId)?.value || '';

            const body = { status_prospek: status, catatan: catatan };
            if (nextFollowup) body.tgl_next_followup = nextFollowup;

            const card = document.getElementById('task-card-' + leadId);
            card.querySelectorAll('.quick-complete-btn').forEach(b => b.disabled = true);

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
                    card.querySelectorAll('.quick-complete-btn').forEach(b => b.disabled = false);
                    showToast('Gagal menyelesaikan follow-up', true);
                }
            })
            .catch(() => {
                card.querySelectorAll('.quick-complete-btn').forEach(b => b.disabled = false);
                showToast('Gagal menyelesaikan follow-up', true);
            });
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

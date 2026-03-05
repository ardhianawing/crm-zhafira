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

                {{-- Status + Selesai Follow-up --}}
                <div class="border-top mt-2 pt-2">
                    {{-- Status buttons (hanya ganti status, tidak complete follow-up) --}}
                    <div class="d-flex gap-1 mb-2">
                        @php
                            $statusColors = ['New'=>'#6c757d','Cold'=>'#0dcaf0','Warm'=>'#ffc107','Hot'=>'#dc3545','Deal'=>'#198754'];
                            $darkText = ['Cold','Warm'];
                        @endphp
                        @foreach($statuses as $status)
                        <button type="button"
                                class="btn flex-fill quick-status-btn"
                                style="
                                    font-size: 0.7rem;
                                    padding: 0.2rem 0;
                                    background-color: {{ $lead->status_prospek->value == $status->value ? $statusColors[$status->value] : 'transparent' }};
                                    color: {{ $lead->status_prospek->value == $status->value ? (in_array($status->value, $darkText) ? '#000' : '#fff') : $statusColors[$status->value] }};
                                    border: 1px solid {{ $statusColors[$status->value] }};
                                "
                                data-lead-id="{{ $lead->id }}"
                                data-status="{{ $status->value }}"
                                {{ $lead->status_prospek->value == $status->value ? 'data-active=true' : '' }}>
                            {{ $status->value }}
                        </button>
                        @endforeach
                    </div>

                    {{-- Catatan inline --}}
                    <div class="collapse mb-2" id="catatan-{{ $lead->id }}">
                        <textarea class="form-control form-control-sm" rows="2" placeholder="Hasil follow-up..." id="catatan-input-{{ $lead->id }}" style="font-size: 0.75rem;"></textarea>
                    </div>

                    @if($lead->fase_followup >= 3)
                    <div class="mb-2">
                        <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-info-circle"></i> Fase 3 — pilih tanggal next:</small>
                        <input type="date" class="form-control form-control-sm mt-1" id="next-followup-{{ $lead->id }}" min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDays(7)->format('Y-m-d') }}" style="font-size: 0.75rem;">
                    </div>
                    @endif

                    {{-- Action buttons --}}
                    <div class="d-flex gap-1">
                        <a class="btn btn-sm flex-fill text-muted" data-bs-toggle="collapse" href="#catatan-{{ $lead->id }}" style="font-size: 0.73rem; border: 1px solid #dee2e6;">
                            <i class="bi bi-chat-left-text"></i> Catatan
                        </a>
                        <button type="button" class="btn btn-sm flex-fill complete-followup-btn" style="background-color: #0f3d2e; color: #fff; font-size: 0.73rem;" data-lead-id="{{ $lead->id }}">
                            <i class="bi bi-check-circle"></i> Selesai Follow-up
                        </button>
                    </div>

                    {{-- Reschedule --}}
                    <div class="d-flex gap-1 mt-1">
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="reschedule-date-{{ $lead->id }}" min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDays(1)->format('Y-m-d') }}" style="font-size: 0.75rem;">
                            <button class="btn btn-outline-secondary reschedule-btn" type="button" data-lead-id="{{ $lead->id }}" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar-event"></i> Reschedule
                            </button>
                        </div>
                    </div>
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

    // Quick Status: hanya ganti status, tidak complete follow-up
    document.querySelectorAll('.quick-status-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            const status = this.dataset.status;
            const card = document.getElementById('task-card-' + leadId);
            const buttons = card.querySelectorAll('.quick-status-btn');

            buttons.forEach(b => b.disabled = true);

            fetch(`/marketing/leads/${leadId}/quick-status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ status_prospek: status }),
            })
            .then(r => r.json())
            .then(data => {
                buttons.forEach(b => b.disabled = false);
                if (data.success) {
                    // Update button styles
                    const statusColors = {New:'#6c757d',Cold:'#0dcaf0',Warm:'#ffc107',Hot:'#dc3545',Deal:'#198754'};
                    const darkText = ['Cold','Warm'];
                    buttons.forEach(b => {
                        const s = b.dataset.status;
                        const isActive = s === status;
                        b.style.backgroundColor = isActive ? statusColors[s] : 'transparent';
                        b.style.color = isActive ? (darkText.includes(s) ? '#000' : '#fff') : statusColors[s];
                        if (isActive) b.setAttribute('data-active', 'true');
                        else b.removeAttribute('data-active');
                    });
                    showToast(data.message);
                } else {
                    showToast('Gagal mengubah status', true);
                }
            })
            .catch(() => { buttons.forEach(b => b.disabled = false); showToast('Gagal mengubah status', true); });
        });
    });

    // Selesai Follow-up: complete follow-up + naikkan fase
    document.querySelectorAll('.complete-followup-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            const card = document.getElementById('task-card-' + leadId);
            const activeStatus = card.querySelector('.quick-status-btn[data-active]');
            const status = activeStatus ? activeStatus.dataset.status : 'New';
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

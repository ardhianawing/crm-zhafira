@props(['lead', 'showActions' => true])

@php
    $statuses = \App\Enums\StatusProspek::cases();
@endphp

<div class="card lead-card mb-2 {{ $lead->isOverdue() ? 'overdue' : '' }}" id="lead-card-{{ $lead->id }}">
    <div class="card-body py-2 px-3">
        {{-- Row 1: Nama + Status --}}
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center min-w-0">
                <strong class="text-truncate" style="font-size: 0.9rem;">{{ $lead->nama_customer }}</strong>
                @if($lead->status_prospek->value == 'Hot')
                    <i class="bi bi-fire text-danger ms-1 flex-shrink-0"></i>
                @endif
                @if($lead->isTransferred())
                    <span class="badge bg-warning text-dark ms-1 flex-shrink-0" style="font-size: 0.6rem; padding: 1px 4px;">OPERAN</span>
                @endif
            </div>
            <div class="dropdown flex-shrink-0 ms-2">
                <button class="btn btn-sm dropdown-toggle p-0 border-0" type="button" data-bs-toggle="dropdown" id="card-status-btn-{{ $lead->id }}">
                    <x-badge-status :status="$lead->status_prospek" />
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($statuses as $status)
                    <li>
                        <a class="dropdown-item card-quick-status {{ $lead->status_prospek->value == $status->value ? 'active' : '' }}"
                           href="#" data-lead-id="{{ $lead->id }}" data-status="{{ $status->value }}">
                            {{ $status->value }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Row 2: Info ringkas 1 baris --}}
        <div class="d-flex align-items-center text-muted mb-1" style="font-size: 0.75rem; gap: 0.5rem;">
            <span><i class="bi bi-phone"></i> {{ $lead->no_hp }}</span>
            <span>·</span>
            <span>F{{ $lead->fase_followup }}</span>
            @if($lead->sumber_lead)
                <span>·</span>
                <span>{{ $lead->sumber_lead }}</span>
            @endif
        </div>

        {{-- Row 3: Follow-up date --}}
        @if($lead->tgl_next_followup)
        <div class="mb-1" style="font-size: 0.75rem;">
            @if($lead->isOverdue())
                <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> {{ $lead->tgl_next_followup->format('d/m/Y') }} · {{ $lead->tgl_next_followup->diffForHumans() }}</span>
            @elseif($lead->isDueToday())
                <span class="text-warning"><i class="bi bi-clock"></i> Hari ini</span>
            @else
                <span class="text-muted"><i class="bi bi-calendar"></i> {{ $lead->tgl_next_followup->format('d/m/Y') }}</span>
            @endif
        </div>
        @endif

        {{-- Row 4: Catatan (jika ada) --}}
        @if($lead->catatan_terakhir)
            <div class="mb-1" style="font-size: 0.75rem;">
                <span class="text-muted">{{ Str::limit($lead->catatan_terakhir, 80) }}</span>
            </div>
        @endif

        {{-- Row 5: Action buttons --}}
        @if($showActions)
            <div class="d-flex gap-2 mt-2">
                <x-whatsapp-dropdown :phone="$lead->no_hp" :lead="$lead" class="btn-sm flex-fill" />
                <a href="{{ route('marketing.leads.show', $lead) }}" class="btn btn-sm flex-fill py-1" style="border-color: #0f3d2e; color: #0f3d2e; font-size: 0.8rem;">
                    <i class="bi bi-eye"></i> Detail
                </a>
            </div>
        @endif
    </div>
</div>

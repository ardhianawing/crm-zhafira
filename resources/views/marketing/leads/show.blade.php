@extends('layouts.app')

@section('title', 'Detail Lead - Zhafira CRM')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="bi bi-person" style="color: #0f3d2e;"></i> Detail Lead
            </h5>
            <a href="{{ route('marketing.leads.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        @php $waTemplate = app(\App\Services\FollowUpService::class)->getWhatsAppTemplate($lead); @endphp

        <!-- Lead Info Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: #0f3d2e; color: #fff;">
                <strong>{{ $lead->nama_customer }}</strong>
                <x-badge-status :status="$lead->status_prospek" />
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size: 0.85rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 35%;">No HP</td>
                            <td>
                                {{ $lead->no_hp }}
                                <x-whatsapp-button :phone="$lead->no_hp" :message="$waTemplate" class="btn-sm ms-1 py-0 px-1" style="font-size: 0.75rem;">Chat</x-whatsapp-button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Fase</td>
                            <td>
                                <span class="badge bg-secondary" style="font-size: 0.7rem;">F{{ $lead->fase_followup }}</span>
                                @if($lead->fase_followup < 3)
                                    <small class="text-muted ms-1">(+{{ [3,5,7][$lead->fase_followup] ?? 7 }} hari)</small>
                                @else
                                    <small class="text-muted ms-1">(Manual)</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Next Follow-up</td>
                            <td>
                                @if($lead->tgl_next_followup)
                                    <span class="{{ $lead->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                        {{ $lead->tgl_next_followup->format('d/m/Y') }}
                                    </span>
                                    @if($lead->isOverdue())
                                        <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Overdue</span>
                                    @elseif($lead->isDueToday())
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Hari ini</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sumber</td>
                            <td>{{ $lead->sumber_lead ?? '-' }}</td>
                        </tr>
                        @if($lead->keterangan)
                        <tr>
                            <td class="text-muted">Keterangan</td>
                            <td>{{ $lead->keterangan }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Dibuat</td>
                            <td>{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($lead->catatan_terakhir)
                        <tr>
                            <td class="text-muted">Catatan</td>
                            <td>{{ $lead->catatan_terakhir }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- WhatsApp Templates -->
        <div class="card mb-3">
            <div class="card-header py-2" style="background-color: #25D366; color: #fff; font-size: 0.9rem;">
                <i class="bi bi-whatsapp"></i> Template WhatsApp
            </div>
            <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                @if($whatsappTemplates->count() > 0)
                    <div class="accordion accordion-flush" id="templateAccordion">
                        @foreach($whatsappTemplates as $index => $template)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button py-1 {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#template{{ $template->id }}" style="font-size: 0.8rem;">
                                    {{ $template->nama_template }}
                                </button>
                            </h2>
                            <div id="template{{ $template->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#templateAccordion">
                                <div class="accordion-body p-2">
                                    @php $renderedTemplate = str_replace('{nama_customer}', $lead->nama_customer, $template->isi_template); @endphp
                                    <pre class="mb-2 p-2 bg-light rounded" style="white-space: pre-wrap; font-family: inherit; font-size: 0.78rem;" id="template-{{ $template->id }}">{{ $renderedTemplate }}</pre>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0" onclick="copyTemplate({{ $template->id }})" style="font-size: 0.73rem;">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                        <x-whatsapp-button :phone="$lead->no_hp" :message="$renderedTemplate" class="btn-sm py-0" style="font-size: 0.73rem;">
                                            Kirim WA
                                        </x-whatsapp-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small">Belum ada template.</p>
                @endif
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card mb-3">
            <div class="card-header py-2" style="background-color: #0f3d2e; color: #fff; font-size: 0.9rem;">
                <i class="bi bi-pencil"></i> Edit Lead
            </div>
            <div class="card-body p-2 p-md-3">
                <form action="{{ route('marketing.leads.update', $lead) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="nama_customer" class="form-label small mb-1">Nama Customer</label>
                            <input type="text" class="form-control form-control-sm" id="nama_customer" name="nama_customer"
                                   value="{{ old('nama_customer', $lead->nama_customer) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_hp" class="form-label small mb-1">No HP</label>
                            <input type="text" class="form-control form-control-sm" id="no_hp" name="no_hp"
                                   value="{{ old('no_hp', $lead->no_hp) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status_prospek" class="form-label small mb-1">Status Prospek</label>
                            <select class="form-select form-select-sm" id="status_prospek" name="status_prospek" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status_prospek', $lead->status_prospek->value) == $status->value ? 'selected' : '' }}>
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="catatan_terakhir" class="form-label small mb-1">Catatan</label>
                            <textarea class="form-control form-control-sm" id="catatan_terakhir" name="catatan_terakhir" rows="2">{{ old('catatan_terakhir', $lead->catatan_terakhir) }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sm mt-2" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
                        <i class="bi bi-save"></i> Update Lead
                    </button>
                </form>
            </div>
        </div>

        <!-- History -->
        <div class="card">
            <div class="card-header py-2" style="background-color: #0f3d2e; color: #fff; font-size: 0.9rem;">
                <i class="bi bi-clock-history"></i> Riwayat
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($lead->histories->take(10) as $history)
                    <div class="list-group-item py-2 px-3" style="font-size: 0.8rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $history->action_label }}</span>
                                @if($history->user)
                                    <small class="text-muted ms-1">{{ $history->user->nama_lengkap }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $history->created_at->format('d/m H:i') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted py-3 small">
                        Belum ada riwayat
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyTemplate(templateId) {
    const text = document.getElementById('template-' + templateId).innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('Template berhasil di-copy!');
    });
}
</script>
@endpush

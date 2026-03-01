@props(['phone', 'lead', 'class' => 'btn-sm', 'label' => 'Chat'])

@php
    $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($normalizedPhone, '0')) {
        $normalizedPhone = '62' . substr($normalizedPhone, 1);
    } elseif (!str_starts_with($normalizedPhone, '62')) {
        $normalizedPhone = '62' . $normalizedPhone;
    }

    $autoTemplate = app(\App\Services\FollowUpService::class)->getWhatsAppTemplate($lead);
    $autoUrl = "https://wa.me/{$normalizedPhone}?text=" . urlencode($autoTemplate);

    // Cache templates (loaded once per request)
    static $waTemplates = null;
    if ($waTemplates === null) {
        $waTemplates = \App\Models\WhatsappTemplate::active()->ordered()->get();
    }
@endphp

<div class="btn-group {{ $class }}" role="group">
    <a href="{{ $autoUrl }}" target="_blank" class="btn {{ $class }} py-1" style="background-color: #25D366; border-color: #25D366; color: #fff; font-size: 0.78rem;">
        <i class="bi bi-whatsapp"></i> {{ $label }}
    </a>
    @if($waTemplates->count() > 0)
    <button type="button" class="btn {{ $class }} dropdown-toggle dropdown-toggle-split py-1" data-bs-toggle="dropdown" style="background-color: #1da851; border-color: #1da851; color: #fff; font-size: 0.78rem;">
        <span class="visually-hidden">Pilih template</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem; max-height: 250px; overflow-y: auto;">
        <li><h6 class="dropdown-header py-1" style="font-size: 0.7rem;">Pilih Template</h6></li>
        @foreach($waTemplates as $tpl)
        @php
            $rendered = str_replace('{nama_customer}', $lead->nama_customer, $tpl->isi_template);
            $tplUrl = "https://wa.me/{$normalizedPhone}?text=" . urlencode($rendered);
        @endphp
        <li>
            <a class="dropdown-item py-1 text-truncate" href="{{ $tplUrl }}" target="_blank" style="max-width: 250px;">
                {{ $tpl->nama_template }}
            </a>
        </li>
        @endforeach
    </ul>
    @endif
</div>

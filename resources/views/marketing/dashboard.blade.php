@extends('layouts.app')

@section('title', 'Dashboard - Zhafira CRM')

@section('content')
@if($todayTasksCount > 0)
<div class="alert alert-warning alert-dismissible fade show py-2 px-3 mb-3" role="alert" style="font-size: 0.82rem; border-left: 4px solid #ffc107;">
    <i class="bi bi-bell-fill me-1"></i>
    <strong>{{ $todayTasksCount }}</strong> follow-up perlu ditindaklanjuti.
    <a href="{{ route('marketing.tasks.today') }}" class="alert-link">Kerjakan →</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.6rem; padding: 0.7rem;"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-speedometer2" style="color: #0f3d2e;"></i> Dashboard
    </h5>
    <small class="text-muted">{{ now()->format('d M Y') }}</small>
</div>

<!-- Statistics Cards -->
<style>
    @media (max-width: 767.98px) {
        .stat-card .card-body { padding: 0.65rem; }
        .stat-card .stat-label { font-size: 0.7rem; }
        .stat-card .stat-value { font-size: 1.25rem; }
        .stat-card .stat-icon { font-size: 1.2rem !important; }
    }
</style>
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="min-w-0">
                        <div class="text-muted mb-1 stat-label">Total Leads</div>
                        <h3 class="mb-0 stat-value">{{ number_format($totalLeads) }}</h3>
                    </div>
                    <div class="stat-icon ms-1 flex-shrink-0">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="min-w-0">
                        <div class="text-muted mb-1 stat-label">Tugas Hari Ini</div>
                        <h3 class="mb-0 stat-value {{ $todaysTasks > 0 ? 'text-warning' : '' }}">{{ number_format($todaysTasks) }}</h3>
                    </div>
                    <div class="stat-icon ms-1 flex-shrink-0">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="min-w-0">
                        <div class="text-muted mb-1 stat-label">Overdue</div>
                        <h3 class="mb-0 stat-value {{ $overdueLeads > 0 ? 'text-danger' : '' }}">{{ number_format($overdueLeads) }}</h3>
                    </div>
                    <div class="stat-icon ms-1 flex-shrink-0">
                        <i class="bi bi-exclamation-triangle {{ $overdueLeads > 0 ? 'text-danger' : '' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="min-w-0">
                        <div class="text-muted mb-1 stat-label">Conversion</div>
                        <h3 class="mb-0 stat-value {{ $conversionRate > 0 ? 'text-success' : '' }}">{{ $conversionRate }}%</h3>
                    </div>
                    <div class="stat-icon ms-1 flex-shrink-0">
                        <i class="bi bi-graph-up-arrow {{ $conversionRate > 0 ? 'text-success' : '' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leads by Status -->
<div class="card mb-4">
    <div class="card-header" style="background-color: #0f3d2e; color: #fff;">
        <i class="bi bi-bar-chart"></i> Status Leads Saya
    </div>
    <div class="card-body p-2 p-md-3">
        <div class="d-flex gap-1 gap-md-2">
            @php
                $statusConfig = [
                    'New'  => ['bg' => '#6c757d', 'fg' => '#fff'],
                    'Cold' => ['bg' => '#0dcaf0', 'fg' => '#000'],
                    'Warm' => ['bg' => '#ffc107', 'fg' => '#000'],
                    'Hot'  => ['bg' => '#dc3545', 'fg' => '#fff'],
                    'Deal' => ['bg' => '#198754', 'fg' => '#fff'],
                    'Tidak Respon' => ['bg' => '#6f42c1', 'fg' => '#fff'],
                    'Tidak Berminat' => ['bg' => '#343a40', 'fg' => '#fff'],
                ];
            @endphp
            @foreach($statusConfig as $status => $colors)
            <div class="flex-fill text-center rounded py-2" style="background-color: {{ $colors['bg'] }}15; border: 1px solid {{ $colors['bg'] }}40;">
                <div class="fw-bold fs-5 mb-0" style="color: {{ $colors['bg'] }};">{{ $leadsByStatus[$status] ?? 0 }}</div>
                <small class="fw-semibold" style="color: {{ $colors['bg'] }}; font-size: 0.7rem;">{{ $status }}</small>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Today's Follow-ups -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #0f3d2e; color: #fff;">
                <span class="text-nowrap"><i class="bi bi-calendar-check"></i> 5 Follow-up Prioritas</span>
                <a href="{{ route('marketing.tasks.today') }}" class="btn btn-sm text-nowrap ms-2" style="background-color: #c9a227; border-color: #c9a227; color: #000; font-size: 0.75rem; padding: 0.2rem 0.5rem;">Lihat Semua</a>
            </div>
            <div class="card-body">
                @forelse($todaysFollowups as $lead)
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between gap-2">
                            <div class="min-w-0">
                                <strong class="d-block text-truncate">{{ $lead->nama_customer }}</strong>
                                <small class="text-muted">
                                    {{ $lead->status_prospek->value }} · F{{ $lead->fase_followup }} ·
                                    <span class="{{ $lead->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                        {{ $lead->isOverdue() ? $lead->tgl_next_followup->diffForHumans() : 'Hari ini' }}
                                    </span>
                                </small>
                            </div>
                            <a href="{{ route('marketing.tasks.today') }}" class="btn btn-sm btn-outline-zhafira flex-shrink-0">
                                Kerjakan
                            </a>
                        </div>
                    </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                    <p class="mb-0">Tidak ada tugas hari ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent News & Quick Actions -->
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header" style="background-color: #0f3d2e; color: #fff;">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('marketing.leads.create') }}" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
                        <i class="bi bi-plus-circle"></i> Tambah Lead Baru
                    </a>
                    <a href="{{ route('marketing.tasks.today') }}" class="btn" style="border-color: #0f3d2e; color: #0f3d2e;">
                        <i class="bi bi-calendar-check"></i> Lihat Tugas Hari Ini
                    </a>
                    <a href="{{ route('marketing.tools') }}" class="btn" style="border-color: #0f3d2e; color: #0f3d2e;">
                        <i class="bi bi-tools"></i> Marketing Tools
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background-color: #0f3d2e; color: #fff;">
                <i class="bi bi-newspaper"></i> Berita Terbaru
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentNews as $news)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $news->judul }}</strong>
                                <br>
                                <small>{{ Str::limit(strip_tags($news->isi_berita), 60) }}</small>
                            </div>
                            <small class="text-muted">{{ $news->tgl_post->format('d/m') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted py-3">
                        Belum ada berita
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('marketing.news.index') }}" style="color: #0f3d2e;">Lihat Semua Berita</a>
            </div>
        </div>
    </div>
</div>

@endsection

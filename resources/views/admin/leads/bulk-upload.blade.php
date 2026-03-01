@extends('layouts.app')

@section('title', 'Bulk Upload Leads - Zhafira CRM')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="mb-0">
                <i class="bi bi-cloud-arrow-up" style="color: #0f3d2e;"></i> Bulk Upload Leads
            </h4>
            <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- Error messages --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Hasil Import --}}
        @if(session('bulk_result'))
            @php $result = session('bulk_result'); @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header" style="background-color: #0f3d2e; color: #fff;">
                    <h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Hasil Import</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="p-3 rounded" style="background-color: rgba(25,135,84,0.1);">
                                <div class="fs-3 fw-bold text-success">{{ $result['success'] }}</div>
                                <small class="text-muted">Berhasil</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded" style="background-color: rgba(255,193,7,0.1);">
                                <div class="fs-3 fw-bold text-warning">{{ $result['duplicates'] }}</div>
                                <small class="text-muted">Duplikat (skip)</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded" style="background-color: rgba(220,53,69,0.1);">
                                <div class="fs-3 fw-bold text-danger">{{ $result['failed'] }}</div>
                                <small class="text-muted">Gagal</small>
                            </div>
                        </div>
                    </div>

                    @if(!empty($result['failed_rows']))
                        <hr>
                        <h6 class="text-danger mb-3"><i class="bi bi-exclamation-circle"></i> Detail Baris Gagal:</h6>
                        {{-- Desktop table --}}
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Baris</th>
                                        <th>Nama</th>
                                        <th>No HP</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result['failed_rows'] as $failedRow)
                                        <tr>
                                            <td>{{ $failedRow['row'] }}</td>
                                            <td>{{ $failedRow['nama'] ?: '-' }}</td>
                                            <td>{{ $failedRow['no_hp'] ?: '-' }}</td>
                                            <td><span class="text-danger">{{ $failedRow['reason'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Mobile cards --}}
                        <div class="d-md-none">
                            @foreach($result['failed_rows'] as $failedRow)
                                <div class="card mb-2 border-danger">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="badge bg-danger">Baris {{ $failedRow['row'] }}</span>
                                        </div>
                                        <div><strong>Nama:</strong> {{ $failedRow['nama'] ?: '-' }}</div>
                                        <div><strong>No HP:</strong> {{ $failedRow['no_hp'] ?: '-' }}</div>
                                        <div class="text-danger mt-1"><small><i class="bi bi-exclamation-circle"></i> {{ $failedRow['reason'] }}</small></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Download Template --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h6 class="mb-1"><i class="bi bi-file-earmark-spreadsheet text-success"></i> Template Upload</h6>
                        <p class="text-muted mb-0 small">Download template CSV, isi datanya, lalu upload.</p>
                    </div>
                    <a href="{{ route('admin.leads.bulk-upload.template') }}" class="btn btn-outline-success w-100 w-md-auto" style="max-width: 250px;">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
            </div>
        </div>

        {{-- Format Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-info-circle" style="color: #0f3d2e;"></i> Format Kolom</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kolom</th>
                                <th>Wajib</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>nama</code></td>
                                <td><span class="badge bg-danger">Wajib</span></td>
                                <td>Nama customer, maks 100 karakter</td>
                            </tr>
                            <tr>
                                <td><code>no_hp</code></td>
                                <td><span class="badge bg-danger">Wajib</span></td>
                                <td>Nomor HP (0xxx / 62xxx / +62xxx), maks 20 karakter</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td><span class="badge bg-secondary">Opsional</span></td>
                                <td>New, Cold, Warm, Hot, atau Deal (default: New)</td>
                            </tr>
                            <tr>
                                <td><code>keterangan</code></td>
                                <td><span class="badge bg-secondary">Opsional</span></td>
                                <td>Catatan tambahan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Upload Form --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background-color: #0f3d2e; color: #fff;">
                <h6 class="mb-0"><i class="bi bi-upload"></i> Upload File</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.leads.bulk-upload.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <div class="upload-area border border-2 border-dashed rounded-3 p-4 text-center mb-3" id="dropZone"
                         style="border-color: #0f3d2e !important; cursor: pointer; transition: all 0.2s;">
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" class="d-none" required>
                        <div id="uploadPlaceholder">
                            <i class="bi bi-cloud-arrow-up" style="font-size: 3rem; color: #0f3d2e;"></i>
                            <p class="mb-1 fw-bold">Drag & drop file di sini</p>
                            <p class="text-muted small mb-2">atau klik untuk browse</p>
                            <span class="badge bg-light text-dark border">.xlsx, .xls, .csv — maks 2MB, 500 baris</span>
                        </div>
                        <div id="fileInfo" class="d-none">
                            <i class="bi bi-file-earmark-check" style="font-size: 2.5rem; color: #198754;"></i>
                            <p class="mb-0 fw-bold text-success" id="fileName"></p>
                            <p class="text-muted small mb-0" id="fileSize"></p>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                                <i class="bi bi-x-circle"></i> Hapus
                            </button>
                        </div>
                    </div>

                    @error('file')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn w-100" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;" id="submitBtn" disabled>
                        <i class="bi bi-cloud-arrow-up"></i> Upload & Import
                    </button>
                </form>
            </div>
        </div>

        <div class="text-muted small mt-3 text-center">
            <i class="bi bi-info-circle"></i> Lead hasil upload masuk sebagai <strong>Unassigned</strong>. Assign ke marketing dari halaman Assignment.
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');

    // Click to browse
    dropZone.addEventListener('click', function(e) {
        if (e.target !== removeFile && !removeFile.contains(e.target)) {
            fileInput.click();
        }
    });

    // Drag & drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = 'rgba(15, 61, 46, 0.05)';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
        });
    });

    dropZone.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            showFileInfo(files[0]);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        }
    });

    removeFile.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        uploadPlaceholder.classList.remove('d-none');
        fileInfo.classList.add('d-none');
        submitBtn.disabled = true;
    });

    function showFileInfo(file) {
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
            'application/csv',
        ];
        const validExt = ['.xlsx', '.xls', '.csv'];
        const ext = '.' + file.name.split('.').pop().toLowerCase();

        if (!validExt.includes(ext)) {
            alert('Format file tidak valid. Gunakan .xlsx, .xls, atau .csv');
            fileInput.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file melebihi 2MB.');
            fileInput.value = '';
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
        uploadPlaceholder.classList.add('d-none');
        fileInfo.classList.remove('d-none');
        submitBtn.disabled = false;
    }

    // Loading state on submit
    uploadForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengimport...';
    });
});
</script>
@endpush

@push('styles')
<style>
    .upload-area:hover {
        background-color: rgba(15, 61, 46, 0.03);
    }
    @media (min-width: 768px) {
        .w-md-auto {
            width: auto !important;
        }
    }
</style>
@endpush
@endsection

@extends('layouts.app_sneat')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-whatsapp text-success me-2"></i>
                        Pengaturan WhatsApp Notification
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-label-{{ $waSettings['enabled'] ? 'success' : 'danger' }} me-2">
                            {{ $waSettings['enabled'] ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Status WhatsApp -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-whatsapp text-primary fs-1"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Status WhatsApp</h6>
                                            <p class="mb-0">
                                                @if($waSettings['enabled'])
                                                    <span class="text-success">✅ Aktif</span>
                                                @else
                                                    <span class="text-danger">❌ Nonaktif</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-token text-info fs-1"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Token API</h6>
                                            <p class="mb-0">
                                                @if($waSettings['token'])
                                                    <span class="text-success">✅ Terkonfigurasi</span>
                                                @else
                                                    <span class="text-danger">❌ Belum Dikonfigurasi</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Pengaturan -->
                    <form action="{{ route('whatsapp.update-settings') }}" method="POST">
                        @csrf
                        
                        <!-- Pengaturan Umum -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <i class="bx bx-cog me-2"></i>
                                    Pengaturan Umum
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled" 
                                           value="1" {{ $waSettings['enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enabled">
                                        Aktifkan WhatsApp Notification
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="typing" name="typing" 
                                           value="1" {{ $waSettings['typing'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="typing">
                                        Tampilkan Indikator Typing
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="country_code" class="form-label">Kode Negara</label>
                                <input type="text" class="form-control" id="country_code" name="country_code" 
                                       value="{{ $waSettings['country_code'] }}" 
                                       placeholder="62">
                                <small class="text-muted">Contoh: 62 untuk Indonesia</small>
                            </div>
                            <div class="col-md-6">
                                <label for="delay" class="form-label">Delay Antar Pesan (detik)</label>
                                <input type="number" class="form-control" id="delay" name="delay" 
                                       value="{{ $waSettings['delay'] }}" 
                                       min="0" max="60">
                                <small class="text-muted">Delay untuk menghindari spam</small>
                            </div>
                            <div class="col-md-12">
                                <label for="token" class="form-label">Token API WhatsApp (Fonnte)</label>
                                <input type="text" class="form-control" id="token" name="token" 
                                       value="{{ $waSettings['token'] }}" placeholder="Masukkan token Fonnte...">
                                <small class="text-muted">Token API dari dashboard Fonnte Anda.</small>
                            </div>
                        </div>

                        <!-- Pengaturan Notifikasi -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <i class="bx bx-bell me-2"></i>
                                    Jenis Notifikasi
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notif_pembayaran" 
                                           name="notifications[pembayaran]" value="1" 
                                           {{ $waSettings['notif_pembayaran'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notif_pembayaran">
                                        <i class="bx bx-money text-success me-2"></i>
                                        Notifikasi Pembayaran
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Kirim notifikasi ketika ada pembayaran baru
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notif_reminder" 
                                           name="notifications[reminder]" value="1" 
                                           {{ $waSettings['notif_reminder'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notif_reminder">
                                        <i class="bx bx-time text-warning me-2"></i>
                                        Reminder Pembayaran
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Kirim pengingat untuk tagihan yang akan jatuh tempo
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notif_konfirmasi" 
                                           name="notifications[konfirmasi]" value="1" 
                                           {{ $waSettings['notif_konfirmasi'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notif_konfirmasi">
                                        <i class="bx bx-check text-success me-2"></i>
                                        Konfirmasi Pembayaran
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Kirim konfirmasi ketika pembayaran berhasil
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notif_sistem" 
                                           name="notifications[sistem]" value="1" 
                                           {{ $waSettings['notif_sistem'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notif_sistem">
                                        <i class="bx bx-cog text-info me-2"></i>
                                        Notifikasi Sistem
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Kirim notifikasi umum untuk maintenance, update, dll
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" onclick="testWhatsApp()">
                                        <i class="bx bx-test-tube me-2"></i>
                                        Test WhatsApp
                                    </button>
                                    <div>
                                        <button type="button" class="btn btn-outline-warning me-2" onclick="resetSettings()">
                                            <i class="bx bx-reset me-2"></i>
                                            Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-2"></i>
                                            Simpan Pengaturan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Test WhatsApp -->
    <div class="modal fade" id="testWhatsAppModal" tabindex="-1" aria-labelledby="testWhatsAppModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="testWhatsAppModalLabel">
                        <i class="bx bx-test-tube me-2"></i>
                        Test WhatsApp Service
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="testForm">
                        <div class="mb-3">
                            <label for="test_number" class="form-label">Nomor WhatsApp</label>
                            <input type="text" class="form-control" id="test_number" name="number" 
                                   placeholder="08123456789" required>
                            <small class="text-muted">Masukkan nomor untuk test (tanpa kode negara)</small>
                        </div>
                        <div class="mb-3">
                            <label for="test_type" class="form-label">Jenis Test</label>
                            <select class="form-select" id="test_type" name="type" required>
                                <option value="system">Pesan Sistem</option>
                                <option value="pembayaran">Notifikasi Pembayaran</option>
                                <option value="reminder">Reminder Pembayaran</option>
                                <option value="konfirmasi">Konfirmasi Pembayaran</option>
                            </select>
                        </div>
                        <div class="mb-3" id="systemFields" style="display: none;">
                            <label for="test_title" class="form-label">Judul Pesan</label>
                            <input type="text" class="form-control" id="test_title" name="title" 
                                   placeholder="Test WhatsApp">
                        </div>
                        <div class="mb-3" id="systemMessageField" style="display: none;">
                            <label for="test_message" class="form-label">Isi Pesan</label>
                            <textarea class="form-control" id="test_message" name="message" rows="3" 
                                      placeholder="Ini adalah pesan test dari sistem SPP"></textarea>
                        </div>
                    </form>
                    <div id="testResult" class="mt-3" style="display: none;">
                        <div class="alert" id="resultAlert">
                            <div id="resultMessage"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="sendTest()">
                        <i class="bx bx-send me-2"></i>
                        Kirim Test
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Toggle system fields berdasarkan jenis test
    document.getElementById('test_type').addEventListener('change', function() {
        const systemFields = document.getElementById('systemFields');
        const systemMessageField = document.getElementById('systemMessageField');
        
        if (this.value === 'system') {
            systemFields.style.display = 'block';
            systemMessageField.style.display = 'block';
            document.getElementById('test_title').required = true;
            document.getElementById('test_message').required = true;
        } else {
            systemFields.style.display = 'none';
            systemMessageField.style.display = 'none';
            document.getElementById('test_title').required = false;
            document.getElementById('test_message').required = false;
        }
    });

    // Test WhatsApp
    function testWhatsApp() {
        const modal = new bootstrap.Modal(document.getElementById('testWhatsAppModal'));
        modal.show();
    }

    // Kirim test
    function sendTest() {
        const form = document.getElementById('testForm');
        const formData = new FormData(form);
        const resultDiv = document.getElementById('testResult');
        const resultAlert = document.getElementById('resultAlert');
        const resultMessage = document.getElementById('resultMessage');

        // Reset result
        resultDiv.style.display = 'none';

        // Kirim request
        fetch('{{ route("whatsapp.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            resultDiv.style.display = 'block';
            
            if (data.success) {
                resultAlert.className = 'alert alert-success';
                resultMessage.innerHTML = `
                    <i class="bx bx-check-circle me-2"></i>
                    ${data.message}
                    <br><small class="text-muted">Message ID: ${data.data?.id?.[0] || 'N/A'}</small>
                `;
            } else {
                resultAlert.className = 'alert alert-danger';
                resultMessage.innerHTML = `
                    <i class="bx bx-error-circle me-2"></i>
                    ${data.message}
                `;
            }
        })
        .catch(error => {
            resultDiv.style.display = 'block';
            resultAlert.className = 'alert alert-danger';
            resultMessage.innerHTML = `
                <i class="bx bx-error-circle me-2"></i>
                Error: ${error.message}
            `;
        });
    }

    // Reset settings
    function resetSettings() {
        if (confirm('Apakah Anda yakin ingin mereset pengaturan WhatsApp ke default?')) {
            // Reset form
            document.getElementById('enabled').checked = false;
            document.getElementById('typing').checked = false;
            document.getElementById('country_code').value = '62';
            document.getElementById('delay').value = '2';
            document.getElementById('notif_pembayaran').checked = true;
            document.getElementById('notif_reminder').checked = true;
            document.getElementById('notif_konfirmasi').checked = true;
            document.getElementById('notif_sistem').checked = true;
        }
    }
</script>
@endsection 
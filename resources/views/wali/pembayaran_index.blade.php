@extends('layouts.app_sneat_wali')

@section('content')
    <div class="container-fluid">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title text-white mb-1">
                                    <i class="fas fa-credit-card me-2"></i>{{ $title }}
                                </h4>
                                <p class="mb-0 opacity-75">Kelola dan pantau pembayaran SPP Anda</p>
                            </div>
                            <div class="text-end">
                                <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="alert-heading mb-1">Informasi Penting</h6>
                            <p class="mb-0">Pembayaran yang dilakukan wali murid akan berstatus "Belum Dikonfirmasi" dan akan dikonfirmasi oleh operator/admin setelah verifikasi bukti pembayaran.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('wali.pembayaran.index') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-search me-1"></i>Cari Siswa
                                    </label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Nama atau NISN siswa" value="{{ $search ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i>Status
                                    </label>
                                    <select name="status_konfirmasi" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="Belum Dikonfirmasi" {{ $status_konfirmasi == 'Belum Dikonfirmasi' ? 'selected' : '' }}>
                                            Belum Dikonfirmasi
                                        </option>
                                        <option value="Sudah Dikonfirmasi" {{ $status_konfirmasi == 'Sudah Dikonfirmasi' ? 'selected' : '' }}>
                                            Sudah Dikonfirmasi
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar me-1"></i>Bulan/Tahun
                                    </label>
                                    <input type="month" name="bulan_tahun" class="form-control" 
                                           value="{{ request('bulan_tahun') ?? date('Y-m') }}">
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill" title="Cari">
                                            <i class="fas fa-search me-1"></i>Cari
                                        </button>
                                        <a href="{{ route('wali.pembayaran.index') }}" class="btn btn-outline-secondary" title="Reset">
                                            <i class="fas fa-refresh"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-list-alt fa-2x text-primary"></i>
                            </div>
                        </div>
                        <h5 class="card-title text-primary mb-1">{{ $pembayaran->total() }}</h5>
                        <p class="card-text text-muted mb-0">Total Pembayaran</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <h5 class="card-title text-success mb-1">{{ $pembayaran->where('status_konfirmasi', 'Sudah Dikonfirmasi')->count() }}</h5>
                        <p class="card-text text-muted mb-0">Sudah Dikonfirmasi</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <h5 class="card-title text-warning mb-1">{{ $pembayaran->where('status_konfirmasi', 'Belum Dikonfirmasi')->count() }}</h5>
                        <p class="card-text text-muted mb-0">Belum Dikonfirmasi</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                            </div>
                        </div>
                        <h5 class="card-title text-info mb-1">{{ formatRupiah($pembayaran->sum('jumlah_dibayar')) }}</h5>
                        <p class="card-text text-muted mb-0">Total Nilai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment List -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-table me-2"></i>Daftar Pembayaran
                            </h6>
                            <small class="text-muted">
                                Menampilkan {{ $pembayaran->count() }} dari {{ $pembayaran->total() }} data
                            </small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($pembayaran->count() > 0)
                            <!-- Desktop Table -->
                            <div class="table-responsive d-none d-lg-block">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="50">No</th>
                                            <th>
                                                <i class="fas fa-calendar me-1"></i>Tanggal
                                            </th>
                                            <th>
                                                <i class="fas fa-user-graduate me-1"></i>Siswa
                                            </th>
                                            <th>
                                                <i class="fas fa-file-invoice me-1"></i>Item Tagihan
                                            </th>
                                            <th class="text-end">
                                                <i class="fas fa-money-bill me-1"></i>Jumlah
                                            </th>
                                            <th class="text-center">
                                                <i class="fas fa-credit-card me-1"></i>Metode
                                            </th>
                                            <th class="text-center">
                                                <i class="fas fa-check-circle me-1"></i>Status
                                            </th>
                                            <th class="text-center" width="150">
                                                <i class="fas fa-cogs me-1"></i>Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = ($pembayaran->currentPage() - 1) * $pembayaran->perPage() + 1; @endphp
                                        @foreach ($pembayaran as $item)
                                            <tr>
                                                <td class="text-center fw-semibold">{{ $no++ }}</td>
                                                <td>
                                                    <span class="fw-semibold">
                                                        {{ formatTanggalIndonesia($item->tanggal_bayar) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="fw-semibold text-dark">{{ $item->tagihan->siswa->nama ?? 'N/A' }}</span>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-id-card me-1"></i>{{ $item->tagihan->siswa->nisn ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $item->tagihan_detail->nama_biaya ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-success">{{ formatRupiah($item->jumlah_dibayar) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-university me-1"></i>Bank Transfer
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($item->status_konfirmasi == 'Sudah Dikonfirmasi')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Dikonfirmasi
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Menunggu
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        @if ($item->bukti_bayar)
                                                            <a href="{{ Storage::url($item->bukti_bayar) }}" target="_blank"
                                                                class="btn btn-sm btn-outline-info" title="Lihat Bukti">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('wali.pembayaran.show', $item->id) }}"
                                                            class="btn btn-sm btn-outline-secondary" title="Detail">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>
                                                        <a href="{{ route('wali.kwitansi.show', $item->id) }}" target="_blank"
                                                            class="btn btn-sm btn-outline-primary" title="Kwitansi">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        @php
                                                            $cancelInfo = canCancelPayment($item, auth()->id());
                                                        @endphp
                                                        @if ($cancelInfo['can_cancel'])
                                                            <button type="button" 
                                                                class="btn btn-sm btn-outline-danger cancel-payment-btn" 
                                                                data-pembayaran-id="{{ $item->id }}"
                                                                data-pembayaran-amount="{{ formatRupiah($item->jumlah_dibayar) }}"
                                                                data-siswa-name="{{ $item->tagihan->siswa->nama }}"
                                                                title="Batalkan Pembayaran">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards -->
                            <div class="d-lg-none">
                                @php $no = ($pembayaran->currentPage() - 1) * $pembayaran->perPage() + 1; @endphp
                                @foreach ($pembayaran as $item)
                                    <div class="card border-0 border-bottom rounded-0 shadow-sm mb-3 mx-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1">
                                                        <i class="fas fa-user-graduate me-1 text-primary"></i>
                                                        {{ $item->tagihan->siswa->nama ?? 'N/A' }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-id-card me-1"></i>{{ $item->tagihan->siswa->nisn ?? 'N/A' }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    @if ($item->status_konfirmasi == 'Sudah Dikonfirmasi')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Dikonfirmasi
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Menunggu
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Tanggal</small>
                                                    <span class="fw-semibold">
                                                        {{ formatTanggalIndonesia($item->tanggal_bayar) }}
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Jumlah</small>
                                                    <span class="fw-bold text-success">{{ formatRupiah($item->jumlah_dibayar) }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Item Tagihan</small>
                                                <span class="fw-semibold">{{ $item->tagihan_detail->nama_biaya ?? 'N/A' }}</span>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-info">
                                                    <i class="fas fa-university me-1"></i>Bank Transfer
                                                </span>
                                                <div class="btn-group" role="group">
                                                    @if ($item->bukti_bayar)
                                                        <a href="{{ Storage::url($item->bukti_bayar) }}" target="_blank"
                                                            class="btn btn-sm btn-outline-info" title="Lihat Bukti">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('wali.pembayaran.show', $item->id) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="Detail">
                                                        <i class="fas fa-info-circle"></i>
                                                    </a>
                                                    <a href="{{ route('wali.kwitansi.show', $item->id) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" title="Kwitansi">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    @php
                                                        $cancelInfo = canCancelPayment($item, auth()->id());
                                                    @endphp
                                                    @if ($cancelInfo['can_cancel'])
                                                        <button type="button" 
                                                            class="btn btn-sm btn-outline-danger cancel-payment-btn" 
                                                            data-pembayaran-id="{{ $item->id }}"
                                                            data-pembayaran-amount="{{ formatRupiah($item->jumlah_dibayar) }}"
                                                            data-siswa-name="{{ $item->tagihan->siswa->nama }}"
                                                            title="Batalkan Pembayaran">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-receipt fa-4x text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-2">Belum Ada Data Pembayaran</h5>
                                <p class="text-muted mb-4">Anda belum memiliki riwayat pembayaran. Silakan lakukan pembayaran melalui menu tagihan.</p>
                                <a href="{{ route('wali.tagihan.index') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Lihat Tagihan
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    @if($pembayaran->count() > 0)
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Menampilkan {{ $pembayaran->firstItem() }} - {{ $pembayaran->lastItem() }} 
                                    dari {{ $pembayaran->total() }} data
                                </div>
                                <div>
                                    {{ $pembayaran->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        
        .btn-group .btn {
            transition: all 0.2s ease;
        }
        
        .btn-group .btn:hover {
            transform: translateY(-1px);
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }
        
        .alert {
            border-radius: 0.75rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
        }
        
        .btn {
            border-radius: 0.5rem;
        }
        
        .bg-opacity-10 {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }
        
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>

    <!-- Cancel Payment Modal -->
    <div class="modal fade" id="cancelPaymentModal" tabindex="-1" aria-labelledby="cancelPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelPaymentModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Pembatalan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Peringatan:</strong> Pembatalan pembayaran tidak dapat dibatalkan kembali.
                    </div>
                    <p>Apakah Anda yakin ingin membatalkan pembayaran berikut?</p>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Siswa:</strong><br>
                            <span id="cancelSiswaName"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Jumlah:</strong><br>
                            <span id="cancelAmount" class="text-danger fw-bold"></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Pembayaran hanya dapat dibatalkan dalam waktu 24 jam setelah dibuat
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                        <i class="fas fa-trash me-1"></i>Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentPembayaranId = null;

            // Handle cancel payment button click
            $('.cancel-payment-btn').on('click', function() {
                const pembayaranId = $(this).data('pembayaran-id');
                const amount = $(this).data('pembayaran-amount');
                const siswaName = $(this).data('siswa-name');

                currentPembayaranId = pembayaranId;
                $('#cancelSiswaName').text(siswaName);
                $('#cancelAmount').text(amount);

                $('#cancelPaymentModal').modal('show');
            });

            // Handle confirm cancel button click
            $('#confirmCancelBtn').on('click', function() {
                if (!currentPembayaranId) return;

                const btn = $(this);
                const originalText = btn.html();
                
                // Disable button and show loading
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');

                // Send AJAX request
                console.log('Sending AJAX request to:', `/walimurid/pembayaran/${currentPembayaranId}/cancel`);
                $.ajax({
                    url: `/walimurid/pembayaran/${currentPembayaranId}/cancel`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        console.log('AJAX request started');
                    },
                    timeout: 30000, // 30 detik timeout
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response.success) {
                            // Show success message
                            alert('Berhasil! ' + response.message);
                            // Reload page to refresh data
                            location.reload();
                        } else {
                            alert('Gagal! ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error response:', xhr);
                        console.log('Status:', status);
                        console.log('Error:', error);
                        
                        let errorMessage = 'Terjadi kesalahan saat membatalkan pembayaran';
                        
                        if (status === 'timeout') {
                            errorMessage = 'Request timeout. Silakan coba lagi.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        } else if (xhr.status === 0) {
                            errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'Halaman tidak ditemukan. Silakan refresh halaman.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Kesalahan server. Silakan coba lagi nanti.';
                        }

                        alert('Gagal! ' + errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        btn.prop('disabled', false).html(originalText);
                        $('#cancelPaymentModal').modal('hide');
                    }
                });
            });

            // Reset current pembayaran ID when modal is hidden
            $('#cancelPaymentModal').on('hidden.bs.modal', function() {
                currentPembayaranId = null;
            });
        });
    </script>
@endsection

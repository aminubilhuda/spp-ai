@extends('layouts.app_sneat_wali')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Informasi Pembayaran</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">Tanggal Pembayaran</td>
                                    <td>: {{ formatTanggalIndonesia($pembayaran->tanggal_bayar) }}</td>
                                </tr>
                                <tr>
                                    <td>Jumlah Dibayar</td>
                                    <td>: {{ formatRupiah($pembayaran->jumlah_dibayar) }}</td>
                                </tr>
                                <tr>
                                    <td>Metode Pembayaran</td>
                                    <td>: {{ $pembayaran->metode_pembayaran }}</td>
                                </tr>
                                <tr>
                                    <td>Status Konfirmasi</td>
                                    <td>: 
                                        @if ($pembayaran->status_konfirmasi == 'Sudah Dikonfirmasi')
                                            <span class="badge bg-label-success">Sudah Dikonfirmasi</span>
                                        @else
                                            <span class="badge bg-label-warning">Belum Dikonfirmasi</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($pembayaran->bank_sekolah)
                                <tr>
                                    <td>Bank Sekolah</td>
                                    <td>: {{ $pembayaran->bank_sekolah->nama_bank }}</td>
                                </tr>
                                @endif
                                @if ($pembayaran->metode_pembayaran == 'Bank Transfer')
                                <tr>
                                    <td>Bank Pengirim</td>
                                    <td>: {{ $pembayaran->bank_pengirim ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>No. Rekening Pengirim</td>
                                    <td>: {{ $pembayaran->no_rekening_pengirim ?? '-' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Informasi Siswa</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">Nama Siswa</td>
                                    <td>: {{ $pembayaran->tagihan->siswa->nama ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>NISN</td>
                                    <td>: {{ $pembayaran->tagihan->siswa->nisn ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Item Tagihan</td>
                                    <td>: {{ $pembayaran->tagihan_detail->nama_biaya ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Total Tagihan</td>
                                    <td>: {{ formatRupiah($pembayaran->tagihan_detail->jumlah_biaya ?? 0) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if ($pembayaran->bukti_bayar)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold">Bukti Pembayaran</h6>
                            <div class="text-center">
                                @if (in_array(pathinfo($pembayaran->bukti_bayar, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ Storage::url($pembayaran->bukti_bayar) }}" 
                                         alt="Bukti Pembayaran" 
                                         class="img-fluid" 
                                         style="max-width: 500px; max-height: 500px;">
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-file-pdf me-2"></i>
                                        <a href="{{ Storage::url($pembayaran->bukti_bayar) }}" 
                                           target="_blank" 
                                           class="btn btn-primary">
                                            Lihat Bukti Pembayaran (PDF)
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if ($pembayaran->user)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold">Informasi Konfirmasi</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">Dikonfirmasi Oleh</td>
                                    <td>: {{ $pembayaran->user->name }}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Konfirmasi</td>
                                    <td>: {{ formatTanggalWaktuIndonesia($pembayaran->updated_at) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('wali.pembayaran.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <div>
                                    @php
                                        $cancelInfo = canCancelPayment($pembayaran, auth()->id());
                                    @endphp
                                    @if ($cancelInfo['can_cancel'])
                                        <button type="button" 
                                            class="btn btn-danger me-2 cancel-payment-btn" 
                                            data-pembayaran-id="{{ $pembayaran->id }}"
                                            data-pembayaran-amount="{{ formatRupiah($pembayaran->jumlah_dibayar) }}"
                                            data-siswa-name="{{ $pembayaran->tagihan->siswa->nama }}">
                                            <i class="fas fa-times me-1"></i>Batalkan Pembayaran
                                        </button>
                                    @endif
                                    <a href="{{ route('wali.kwitansi.show', $pembayaran->id) }}"
                                       target="_blank" 
                                       class="btn btn-primary">
                                        <i class="fas fa-print"></i> Cetak Kwitansi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            // Redirect to pembayaran index
                            window.location.href = '{{ route("wali.pembayaran.index") }}';
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
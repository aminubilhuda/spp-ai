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
                                    <td>: {{ $pembayaran->tanggal_bayar ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d/m/Y') : '-' }}</td>
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
                                    <td>: {{ $pembayaran->updated_at ? \Carbon\Carbon::parse($pembayaran->updated_at)->format('d/m/Y H:i') : '-' }}</td>
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
                                <a href="{{ route('wali.kwitansi_pembayaran.show', $pembayaran->id) }}" 
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
@endsection 
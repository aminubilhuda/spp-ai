@extends('layouts.app_sneat')

@section('content')
    <div class="row">
        <!-- Payment Statistics -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Pembayaran Hari Ini</h5>
                    <small class="text-muted float-end">{{ date('d M Y') }}</small>
                </div>
                <div class="card-body">
                    <h3 class="text-primary">{{ formatRupiah($paymentStats['today']) }}</h3>
                    <p class="mb-0">Total Pembayaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Pembayaran Minggu Ini</h5>
                    <small class="text-muted float-end">{{ Carbon\Carbon::now()->startOfWeek()->format('d M') }} -
                        {{ Carbon\Carbon::now()->endOfWeek()->format('d M') }}</small>
                </div>
                <div class="card-body">
                    <h3 class="text-info">{{ formatRupiah($paymentStats['week']) }}</h3>
                    <p class="mb-0">Total Pembayaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Pembayaran Bulan Ini</h5>
                    <small class="text-muted float-end">{{ Carbon\Carbon::now()->format('F Y') }}</small>
                </div>
                <div class="card-body">
                    <h3 class="text-success">{{ formatRupiah($paymentStats['month']) }}</h3>
                    <p class="mb-0">Total Pembayaran</p>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Status Tagihan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded-pill bg-label-success me-3">
                                    <i class="bx bx-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Lunas</h6>
                                    <small>{{ $statusCount['lunas'] ?? 0 }} tagihan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded-pill bg-label-warning me-3">
                                    <i class="bx bx-timer"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Diangsur</h6>
                                    <small>{{ $statusCount['angsur'] ?? 0 }} tagihan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded-pill bg-label-danger me-3">
                                    <i class="bx bx-x"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Belum Lunas</h6>
                                    <small>{{ $statusCount['belum_lunas'] ?? 0 }} tagihan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded-pill bg-label-primary me-3">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Total Siswa</h6>
                                    <small>{{ $totalSiswa }} siswa</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Total Tagihan</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="text-danger mb-1">{{ formatRupiah($sisaTagihan) }}</h3>
                        <p class="mb-0">Sisa Tagihan yang Belum Dibayar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pembayaran Terbaru</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Tagihan</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->tanggal_bayar ? Carbon\Carbon::parse($payment->tanggal_bayar)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $payment->tagihan->siswa->nama ?? 'N/A' }}</td>
                                    <td>{{ $payment->tagihan_detail->nama_biaya ?? 'N/A' }}</td>
                                    <td>{{ formatRupiah($payment->jumlah_dibayar) }}</td>
                                    <td>{{ $payment->metode_pembayaran }}</td>
                                    <td>
                                        @if ($payment->status_konfirmasi == 'Sudah Dikonfirmasi')
                                            <span class="badge bg-success">Dikonfirmasi</span>
                                        @else
                                            <span class="badge bg-warning">Belum Dikonfirmasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada pembayaran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

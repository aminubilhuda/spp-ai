@extends('layouts.app_sneat')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold py-3 mb-4">{{ $title }}</h3>

    {{-- Bagian Statistik --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-user bx-sm rounded-circle bg-label-primary p-2"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Siswa</span>
                    <h3 class="card-title mb-2">{{ $totalSiswa }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-money bx-sm rounded-circle bg-label-warning p-2"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Tagihan</span>
                    <small class="text-muted d-block">Tahun Pelajaran {{ $tahunAktif?->nama ?? '-' }}</small>
                    <h3 class="card-title mb-2">{{ formatRupiah($totalTagihanSetahun) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-file bx-sm rounded-circle bg-label-info p-2"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Tagihan Bulan Ini</span>
                    <h3 class="card-title mb-2">{{ $totalTagihanBulanIni }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-wallet bx-sm rounded-circle bg-label-success p-2"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Uang Masuk</span>
                    <h3 class="card-title mb-2">{{ formatRupiah($totalPembayaranTerkonfirmasi) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-money-withdraw bx-sm rounded-circle bg-label-danger p-2"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Uang Keluar</span>
                    <h3 class="card-title mb-2">{{ formatRupiah($totalKasKeluar) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-trending-up bx-sm rounded-circle bg-label-info p-2"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Sisa Tagihan {{ now()->year }}</span>
                    <h3 class="card-title mb-2">{{ formatRupiah($totalTagihanSetahun - $totalPembayaranTerkonfirmasi) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Grafik Pembayaran --}}
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">Grafik Uang Masuk Tahun Ini</div>
                <div class="card-body">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Rincian Tagihan --}}
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Rincian Tagihan {{ now()->year }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Jenis Biaya</th>
                                <th>Total</th>
                                <th>Siswa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rincianTagihan as $rincian)
                            <tr>
                                <td>{{ $rincian->nama_biaya }}</td>
                                <td>{{ formatRupiah($rincian->total_biaya) }}</td>
                                <td>{{ $rincian->jumlah_siswa }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td>{{ formatRupiah($totalTagihanSetahun) }}</td>
                                <td>{{ $totalSiswa }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Statistik Ketepatan Pembayaran --}}
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Statistik Ketepatan Pembayaran {{ now()->year }}</div>
                <div class="card-body">
                    <canvas id="paymentStatsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Pembayaran Menunggu Konfirmasi --}}
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Pembayaran Menunggu Konfirmasi</div>
                <div class="card-body">
                    @if($pembayaranMenungguKonfirmasi->isEmpty())
                        <p class="text-center">Tidak ada pembayaran yang menunggu konfirmasi.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($pembayaranMenungguKonfirmasi as $pembayaran)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $pembayaran->tagihan->siswa->nama }}</strong><br>
                                        <small>{{ formatRupiah($pembayaran->jumlah_dibayar) }} - {{ $pembayaran->metode_pembayaran }}</small>
                                    </div>
                                    <a href="{{ route('pembayaran.index', ['search' => $pembayaran->tagihan->siswa->nama]) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Grafik Pembayaran Bulanan
    const ctx = document.getElementById('paymentChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Total Pembayaran',
                data: @json($chartData),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Grafik Donut Ketepatan Pembayaran
    const ctxStats = document.getElementById('paymentStatsChart');
    new Chart(ctxStats, {
        type: 'doughnut',
        data: {
            labels: ['Tepat Waktu', 'Terlambat'],
            datasets: [{
                data: [{{ $pembayaranTepatWaktu }}, {{ $pembayaranTerlambat }}],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush

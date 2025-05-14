@extends('layouts.app_sneat')

@section('styles')
    <style>
        .card-spp {
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card-spp-header {
            background-color: #f5f5f9;
            padding: 10px 15px;
            border-bottom: 1px solid #ccc;
            border-radius: 8px 8px 0 0;
        }

        .card-spp-body {
            padding: 15px;
        }

        .month-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            position: relative;
        }

        .month-box.paid {
            border-color: #71dd37;
            background-color: #f0f9e8;
        }

        .month-box.paid:after {
            content: "✓";
            position: absolute;
            top: 8px;
            right: 10px;
            color: #71dd37;
            font-weight: bold;
        }

        .month-box.partial {
            border-color: #ffab00;
            background-color: #fff8e8;
        }

        .month-box.partial:after {
            content: "⌛";
            position: absolute;
            top: 8px;
            right: 10px;
            color: #ffab00;
        }

        .month-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .month-amount {
            font-size: 14px;
        }

        .month-status {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <a href="{{ route('tagihan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="width: 30%">Nama Siswa</td>
                                    <td>: <strong>{{ $siswa->nama }}</strong></td>
                                </tr>
                                <tr>
                                    <td>NISN</td>
                                    <td>: {{ $siswa->nisn }}</td>
                                </tr>
                                <tr>
                                    <td>Kelas</td>
                                    <td>: {{ $siswa->kelas }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="width: 30%">Angkatan</td>
                                    <td>: {{ $siswa->angkatan }}</td>
                                </tr>
                                <tr>
                                    <td>Jurusan</td>
                                    <td>: {{ $siswa->jurusan->nama ?? 'Data tidak tersedia' }}</td>
                                </tr>
                                <tr>
                                    <td>Jumlah Tagihan</td>
                                    <td>: <span class="badge bg-primary">@php
                                        $totalTagihanDetails = 0;
                                        foreach ($tagihan as $item) {
                                            $totalTagihanDetails += $item->tagihan_details->count();
                                        }
                                        echo $totalTagihanDetails;
                                    @endphp</span> Tagihan</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu SPP -->
            <div class="card mb-4">
                <h5 class="card-header">Kartu SPP Tahun {{ date('Y') }}</h5>
                <div class="card-body">
                    @php
                        $namaBulan = [
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ];

                        // Mengelompokkan tagihan berdasarkan bulan
                        $tagihanByBulan = [];
                        foreach ($tagihan as $item) {
                            if ($item->tanggal_tagihan) {
                                $bulan = \Carbon\Carbon::parse($item->tanggal_tagihan)->format('m');
                                $tagihanByBulan[$bulan][] = $item;
                            }
                        }
                    @endphp

                    <div class="row">
                        @foreach ($namaBulan as $kodeBulan => $namaBulan)
                            <div class="col-md-3">
                                @php
                                    $status = 'unpaid';
                                    $totalBulan = 0;
                                    $tagihanBulan = $tagihanByBulan[$kodeBulan] ?? [];

                                    // Hitung total dan cek status pembayaran
                                    foreach ($tagihanBulan as $item) {
                                        // Calculate total from tagihan_details
                                        foreach ($item->tagihan_details as $detail) {
                                            $totalBulan += $detail->jumlah_biaya;
                                        }
                                        if ($item->status == 'lunas') {
                                            $status = 'paid';
                                        } elseif ($item->status == 'angsur' && $status != 'paid') {
                                            $status = 'partial';
                                        }
                                    }
                                @endphp

                                <div class="month-box {{ $status }}">
                                    <div class="month-title">{{ $namaBulan }}</div>
                                    @if (count($tagihanBulan) > 0)
                                        <div class="month-amount">{{ formatRupiah($totalBulan) }}</div>
                                        @if ($status == 'paid')
                                            <div class="badge bg-success">LUNAS</div>
                                        @elseif($status == 'partial')
                                            <div class="badge bg-warning">ANGSUR</div>
                                        @else
                                            <div class="badge bg-danger">BELUM BAYAR</div>
                                        @endif
                                    @else
                                        <div class="text-muted small">Belum ada tagihan</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <h5 class="card-header">Daftar Tagihan Siswa</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tagihan</th>
                                    <th>Periode</th>
                                    <th>Tanggal Tagihan</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse($tagihan as $item)
                                    @foreach ($item->tagihan_details as $detail)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>
                                                <strong>{{ $detail->nama_biaya }}</strong>
                                            </td>
                                            <td>
                                                @if ($item->tanggal_tagihan && $item->tanggal_jatuh_tempo)
                                                    @php
                                                        $bulan = \Carbon\Carbon::parse($item->tanggal_tagihan)->format(
                                                            'm',
                                                        );
                                                        $tahun = \Carbon\Carbon::parse($item->tanggal_tagihan)->format(
                                                            'Y',
                                                        );
                                                        $namaBulan = [
                                                            '01' => 'Jan',
                                                            '02' => 'Feb',
                                                            '03' => 'Mar',
                                                            '04' => 'Apr',
                                                            '05' => 'Mei',
                                                            '06' => 'Jun',
                                                            '07' => 'Jul',
                                                            '08' => 'Agu',
                                                            '09' => 'Sep',
                                                            '10' => 'Okt',
                                                            '11' => 'Nov',
                                                            '12' => 'Des',
                                                        ];
                                                    @endphp
                                                    {{ $namaBulan[$bulan] }} {{ $tahun }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $item->tanggal_tagihan ? \Carbon\Carbon::parse($item->tanggal_tagihan)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td>{{ $item->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td><strong>{{ formatRupiah($detail->jumlah_biaya) }}</strong></td>
                                            <td>
                                                @if ($item->status == 'baru')
                                                    <span class="badge bg-primary">Baru</span>
                                                @elseif($item->status == 'angsur')
                                                    <span class="badge bg-warning">Angsur</span>
                                                @elseif($item->status == 'lunas')
                                                    <span class="badge bg-success">Lunas</span>
                                                @else
                                                    <span class="badge bg-danger">Belum Lunas</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route($routePrefix . '.show', $item->id) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="bx bx-show-alt"></i>
                                                    </a>
                                                    <a href="{{ route($routePrefix . '.edit', $item->id) }}"
                                                        class="btn btn-warning btn-sm">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </a>
                                                    <form action="{{ route('tagihan.destroyDetail', $detail->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus item tagihan ini?')"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="my-4">
                                                <i class="bx bx-file-find bx-lg text-muted"></i>
                                                <p class="text-muted mt-2">Belum ada tagihan untuk siswa ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @if ($tagihan->count() > 0)
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-end"><strong>Total Tagihan:</strong></td>
                                        <td>
                                            <strong>
                                                @php
                                                    $grandTotal = 0;
                                                    foreach ($tagihan as $item) {
                                                        $grandTotal += $item->tagihan_details->sum('jumlah_biaya');
                                                    }
                                                @endphp
                                                {{ formatRupiah($grandTotal) }}
                                            </strong>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

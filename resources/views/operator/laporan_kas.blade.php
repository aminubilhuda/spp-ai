@extends('layouts.app_sneat')

@section('content')
<div class="row justify-content-center">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">{{ $title ?? 'Laporan Kas Sekolah' }}</h5>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-body bg-success text-white mb-2">
                            <div>Total Uang Masuk</div>
                            <h5>Rp {{ number_format($totalMasuk,0,',','.') }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-body bg-danger text-white mb-2">
                            <div>Total Pengeluaran</div>
                            <h5>Rp {{ number_format($totalKeluar,0,',','.') }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-body bg-primary text-white mb-2">
                            <div>Saldo Kas</div>
                            <h5>Rp {{ number_format($saldo,0,',','.') }}</h5>
                        </div>
                    </div>
                </div>
                <h5 class="mt-4 mb-2">Histori Pengeluaran Kas</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Operator</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse($histori as $item)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>Rp {{ number_format($item->jumlah,0,',','.') }}</td>
                                <td>{{ $item->kategori }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>{{ $item->user->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data pengeluaran</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
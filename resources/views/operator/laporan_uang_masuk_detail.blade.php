@extends('layouts.app_sneat', ['title' => $title])

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">{{ $title }}</h5>
            </div>
            <div class="card-body">
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm mb-3"><i class="bx bx-arrow-back"></i> Kembali</a>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembayaran as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d/m/Y') }}</td>
                                    <td>{{ $item->tagihan->siswa->nama ?? '-' }}</td>
                                    <td>{{ formatRupiah($item->jumlah_dibayar) }}</td>
                                    <td>{{ $item->metode_pembayaran }}</td>
                                    <td>
                                        <span class="badge bg-success">Sudah Dikonfirmasi</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data pembayaran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <strong>Total:</strong> {{ formatRupiah($pembayaran->sum('jumlah_dibayar')) }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
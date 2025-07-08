@extends('layouts.app_sneat')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Laporan Rekap Pembayaran</h5>
        @if(isset($tahunPelajarans) && isset($tahunPelajaranId))
            @php
                $tahunDipilih = $tahunPelajarans->firstWhere('id', $tahunPelajaranId);
            @endphp
            <div class="mb-2">
                <span class="badge bg-info">Tahun Pelajaran: {{ $tahunDipilih->nama ?? '-' }}</span>
            </div>
        @endif
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Tanggal Bayar</th>
                    <th>Status</th>
                    <th>Jumlah Dibayar</th>
                    <th>Bank Sekolah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pembayaran as $item)
                <tr>
                    <td>{{ $item->tagihan->siswa->nama ?? '-' }}</td>
                    <td>{{ $item->tagihan->siswa->kelas ?? '-' }}</td>
                    <td>{{ $item->tagihan->siswa->jurusan->nama ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_bayar)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ $item->status_konfirmasi }}</td>
                    <td>Rp{{ number_format($item->jumlah_dibayar,0,',','.') }}</td>
                    <td>{{ $item->bank_sekolah->nama ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3 text-end">
            <strong>Total Pembayaran Masuk: Rp{{ number_format($pembayaran->sum('jumlah_dibayar'),0,',','.') }}</strong>
        </div>
    </div>
</div>
@endsection 
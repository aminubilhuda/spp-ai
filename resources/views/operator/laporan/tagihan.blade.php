@extends('layouts.app_sneat')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Laporan Tagihan</h5>
        @if(isset($tahunAktif) && isset($tahunPelajaranId))
            @php
                $tahunDipilih = $tahunAktif;
                if($tahunPelajaranId != $tahunAktif->id && isset($tagihan) && count($tagihan) > 0) {
                    $tahunDipilih = optional($tagihan->first()->tahunPelajaran);
                }
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
                    <th>Tanggal Tagihan</th>
                    <th>Status</th>
                    <th>Detail Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tagihan as $item)
                <tr>
                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas ?? '-' }}</td>
                    <td>{{ $item->siswa->jurusan->nama ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_tagihan)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ $item->status }}</td>
                    <td>
                        <ul>
                        @foreach($item->tagihan_details as $detail)
                            <li>{{ $detail->nama_biaya }}: Rp{{ number_format($detail->jumlah_biaya,0,',','.') }}</li>
                        @endforeach
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 
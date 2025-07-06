@extends('layouts.app_sneat_wali')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">{{ $title }}</h5>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <td width="30%">ID SISWA</td>
                                <td>: {{ $siswa->id }}</td>
                            </tr>
                            <tr>
                                <td>NAMA LENGKAP</td>
                                <td>: {{ $siswa->nama }}</td>
                            </tr>
                            <tr>
                                <td>NISN</td>
                                <td>: {{ $siswa->nisn }}</td>
                            </tr>
                            <tr>
                                <td>NIS</td>
                                <td>: {{ $siswa->nis }}</td>
                            </tr>
                            <tr>
                                <td>JURUSAN</td>
                                <td>: {{ $siswa->jurusan->nama }}</td>
                            </tr>
                            <tr>
                                <td>KELAS</td>
                                <td>: {{ $siswa->kelas }}</td>
                            </tr>
                            <tr>
                                <td>ANGKATAN</td>
                                <td>: {{ $siswa->angkatan }}</td>
                            </tr>
                            <tr>
                                <td>TOTAL TAGIHAN</td>
                                <td>: {{ formatRupiah($total_tagihan) }}</td>
                            </tr>
                            <tr>
                                <td>TOTAL PEMBAYARAN</td>
                                <td>: {{ formatRupiah($total_pembayaran) }}</td>
                            </tr>
                            <tr>
                                <td>SISA TAGIHAN</td>
                                <td>: {{ formatRupiah($total_tagihan - $total_pembayaran) }}</td>
                            </tr>
                        </thead>
                    </table>
                </div>

                <h5 class="mt-4">Riwayat Tagihan</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>TANGGAL</th>
                                <th>NAMA BIAYA</th>
                                <th>JUMLAH BIAYA</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswa->tagihan as $tagihan)
                                @foreach($tagihan->tagihan_details as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $tagihan->tanggal_tagihan->format('d/m/Y') }}</td>
                                    <td>{{ $detail->nama_biaya }}</td>
                                    <td>{{ formatRupiah($detail->jumlah_biaya) }}</td>
                                    <td>
                                        @if($detail->status == 'lunas')
                                            <span class="badge bg-success">Lunas</span>
                                        @elseif($detail->status == 'angsur')
                                            <span class="badge bg-warning">Diangsur</span>
                                        @else
                                            <span class="badge bg-danger">Belum Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-4">Riwayat Pembayaran</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>TANGGAL</th>
                                <th>JUMLAH BAYAR</th>
                                <th>METODE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswa->tagihan as $tagihan)
                                @foreach($tagihan->pembayaran as $pembayaran)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $pembayaran->tanggal_bayar->format('d/m/Y') }}</td>
                                    <td>{{ formatRupiah($pembayaran->jumlah_dibayar) }}</td>
                                    <td>{{ $pembayaran->metode_pembayaran }}</td>
                                    <td>
                                        @if($pembayaran->status_konfirmasi == 'sudah_dikonfirmasi')
                                            <span class="badge bg-success">Dikonfirmasi</span>
                                        @elseif($pembayaran->status_konfirmasi == 'belum_dikonfirmasi')
                                            <span class="badge bg-warning">Belum Dikonfirmasi</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="{{ route('wali.siswa.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
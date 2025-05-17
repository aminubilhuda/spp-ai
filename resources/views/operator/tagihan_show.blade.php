@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Detail Tagihan</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <td width="20%">Nama Siswa</td>
                                <td>: {{ $tagihan->siswa->nama }}</td>
                            </tr>
                            <tr>
                                <td>Kelas</td>
                                <td>: {{ $tagihan->kelas }}</td>
                            </tr>
                            <tr>
                                <td>Angkatan</td>
                                <td>: {{ $tagihan->angkatan }}</td>
                            </tr>
                            <tr>
                                <td>Jurusan</td>
                                <td>: {{ $tagihan->jurusan }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Tagihan</td>
                                <td>: {{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Jatuh Tempo</td>
                                <td>: {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>: {{ $tagihan->status_tagihan }}</td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>: {{ $tagihan->keterangan }}</td>
                            </tr>
                        </table>

                        <h5 class="mt-4">Detail Biaya</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Biaya</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total = 0;
                                @endphp
                                @foreach ($tagihan->tagihan_details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->nama_biaya }}</td>
                                        <td>{{ formatRupiah($detail->jumlah_biaya) }}</td>
                                    </tr>
                                    @php
                                        $total += $detail->jumlah_biaya;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total</strong></td>
                                    <td><strong>{{ formatRupiah($total) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('tagihan.index') }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection

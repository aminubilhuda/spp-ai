@extends('layouts.app_sneat_wali')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Siswa</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">Nama Siswa</td>
                                    <td>: {{ $tagihan->siswa->nama ?? 'Data tidak ditemukan' }}</td>
                                </tr>
                                <tr>
                                    <td>NISN</td>
                                    <td>: {{ $tagihan->siswa->nisn ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>NIS</td>
                                    <td>: {{ $tagihan->siswa->nis ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Jurusan</td>
                                    <td>: {{ $tagihan->siswa->jurusan->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Kelas</td>
                                    <td>: {{ $tagihan->siswa->kelas ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Tagihan</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">Tanggal Tagihan</td>
                                    <td>:
                                        {{ $tagihan->tanggal_tagihan ? \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d/m/Y') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jatuh Tempo</td>
                                    <td>:
                                        {{ $tagihan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Keterangan</td>
                                    <td>: {{ $tagihan->keterangan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Denda</td>
                                    <td>: {{ formatRupiah($tagihan->denda) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Ringkasan Pembayaran</h6>
                                    @php
                                        $totalTagihan = $tagihan->tagihan_details->sum('jumlah_biaya');
                                        $totalLunas = 0;
                                        $totalAngsur = 0;
                                        $totalBelumLunas = 0;
                                        $totalBaru = 0;
                                        $totalSisaSeluruhDetail = 0;

                                        foreach ($tagihan->tagihan_details as $detail) {
                                            // Hitung total pembayaran yang sudah dikonfirmasi
                                            $totalDibayar = $detail
                                                ->pembayaran()
                                                ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                                ->sum('jumlah_dibayar');
                                            $sisaBayar = $detail->jumlah_biaya - $totalDibayar;
                                            $totalSisaSeluruhDetail += max(0, $sisaBayar);

                                            if ($sisaBayar <= 0) {
                                                $totalLunas += $detail->jumlah_biaya;
                                            } elseif ($totalDibayar > 0) {
                                                $totalAngsur += $detail->jumlah_biaya;
                                            } elseif ($detail->status == 'baru') {
                                                $totalBaru += $detail->jumlah_biaya;
                                            } else {
                                                $totalBelumLunas += $detail->jumlah_biaya;
                                            }
                                        }

                                        $progress = $totalTagihan > 0 ? ($totalLunas / $totalTagihan) * 100 : 0;
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h5 class="text-success">{{ formatRupiah($totalLunas) }}</h5>
                                                <small class="text-muted">Sudah Dibayar</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h5 class="text-info">{{ formatRupiah($totalAngsur) }}</h5>
                                                <small class="text-muted">Diangsur</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h5 class="text-warning">{{ formatRupiah($totalSisaSeluruhDetail) }}</h5>
                                                <small class="text-muted">Sisa Bayar</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h5 class="text-primary">{{ formatRupiah($totalTagihan) }}</h5>
                                                <small class="text-muted">Total Tagihan</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                {{ number_format($progress, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">Progress pembayaran</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6>Detail Biaya</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Biaya</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = 1; @endphp
                                        @forelse ($tagihan->tagihan_details as $detail)
                                            @php
                                                $totalDibayar = $detail
                                                    ->pembayaran()
                                                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                                    ->sum('jumlah_dibayar');
                                                $sisaBayar = $detail->jumlah_biaya - $totalDibayar;

                                                if ($sisaBayar <= 0) {
                                                    $statusDisplay = 'lunas';
                                                } elseif ($totalDibayar > 0) {
                                                    $statusDisplay = 'angsur';
                                                } elseif ($detail->status == 'baru') {
                                                    $statusDisplay = 'baru';
                                                } else {
                                                    $statusDisplay = 'belum_lunas';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>{{ $detail->nama_biaya }}</td>
                                                <td>{{ formatRupiah($detail->jumlah_biaya) }}</td>
                                                <td>
                                                    @if ($statusDisplay == 'lunas')
                                                        <span class="badge bg-label-success">Lunas</span>
                                                    @elseif ($statusDisplay == 'angsur')
                                                        <span class="badge bg-label-info">Diangsur</span>
                                                    @elseif ($statusDisplay == 'belum_lunas')
                                                        <span class="badge bg-label-warning">Belum Lunas</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">Baru</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($statusDisplay == 'lunas')
                                                        <span class="text-success">Rp 0</span>
                                                    @elseif ($statusDisplay == 'angsur')
                                                        <span class="text-info">Diangsur</span>
                                                    @else
                                                        <span class="text-warning">{{ formatRupiah($sisaBayar) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada detail biaya</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <th colspan="2" class="text-end">Total:</th>
                                            <th>{{ formatRupiah($tagihan->tagihan_details->sum('jumlah_biaya')) }}</th>
                                            <th></th>
                                            <th class="text-warning">{{ formatRupiah($totalSisaSeluruhDetail) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <a href="{{ route('wali.tagihan.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back"></i> Kembali
                                </a>
                                <a href="{{ route('panduan.pembayaran', ['id' => $tagihan->id]) }}" class="btn btn-info" target="_blank">
                                    <i class="bx bx-help-circle"></i> Panduan Pembayaran
                                </a>
                                <a href="{{ route('wali.invoice.show', ['id' => $tagihan->id]) }}" class="btn btn-primary" target="_blank">
                                    <i class="bx bx-receipt"></i> Lihat Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

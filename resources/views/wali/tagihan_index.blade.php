@extends('layouts.app_sneat_wali')

@section('content')
    <div class="row justify-content-center">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('wali.tagihan.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari tagihan (nama siswa, NISN, nama biaya)"
                                        value="{{ $search ?? '' }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    @if (isset($search) && $search != '')
                                        <a href="{{ route('wali.tagihan.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama Siswa</td>
                                    <td>NISN</td>
                                    <td>Total</td>
                                    <td>Status</td>
                                    {{-- <td>Jatuh Tempo</td> --}}
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse ($tagihan as $item)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $item->siswa->nama ?? 'Data siswa tidak ditemukan' }}</td>
                                        <td>{{ $item->siswa->nisn ?? '-' }}</td>
                                        <td>{{ formatRupiah($item->tagihan_details->sum('jumlah_biaya')) }}</td>
                                        <td>
                                            @php
                                                $totalDetails = $item->tagihan_details->count();
                                                $lunasCount = $item->tagihan_details->where('status', 'lunas')->count();
                                                $angsurCount = $item->tagihan_details
                                                    ->where('status', 'angsur')
                                                    ->count();
                                                $belumLunasCount = $item->tagihan_details
                                                    ->where('status', 'belum_lunas')
                                                    ->count();
                                                $baruCount = $item->tagihan_details->where('status', 'baru')->count();

                                                if ($lunasCount == $totalDetails) {
                                                    $overallStatus = 'lunas';
                                                } elseif ($belumLunasCount > 0 || $baruCount > 0) {
                                                    $overallStatus = 'belum_lunas';
                                                } elseif ($angsurCount > 0) {
                                                    $overallStatus = 'angsur';
                                                } else {
                                                    $overallStatus = 'baru';
                                                }
                                            @endphp

                                            @if ($overallStatus == 'lunas')
                                                <span class="badge bg-label-success">Lunas</span>
                                            @elseif ($overallStatus == 'angsur')
                                                <span class="badge bg-label-info">Diangsur</span>
                                            @elseif ($overallStatus == 'belum_lunas')
                                                <span class="badge bg-label-warning">Belum Lunas</span>
                                            @else
                                                <span class="badge bg-label-secondary">Baru</span>
                                            @endif

                                            <br>
                                            <small class="text-muted">
                                                {{ $lunasCount }}/{{ $totalDetails }} lunas
                                            </small>
                                        </td>
                                        {{-- <td>{{ $item->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                        </td> --}}
                                        <td>
                                            <a href="{{ route('wali.tagihan.show', $item->id) }}"
                                                class="btn btn-sm btn-info"> <i class="fas fa-eye"></i> Detail</a>
                                            @if ($overallStatus != 'lunas')
                                                {{-- <a href="{{ route('wali.pembayaran.create', ['tagihan_id' => $item->id]) }}"
                                                     class="btn btn-sm btn-success"> <i class="fas fa-credit-card"></i>
                                                     Bayar</a> --}}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data tagihan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="card-footer">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

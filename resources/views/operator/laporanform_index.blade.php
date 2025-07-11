@extends('layouts.app_sneat')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h3 class="mb-1">Laporan Keuangan SPP</h3>
            <p class="text-muted">Silakan pilih parameter untuk menampilkan laporan tagihan atau pembayaran sesuai kebutuhan Anda.</p>
        </div>
    </div>
    <!-- Ringkasan Uang Masuk -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Uang Masuk Hari Ini</h6>
                    <h3 class="text-success mb-0">{{ formatRupiah($totalHariIni ?? 0) }}</h3>
                    <a href="{{ route('laporan.uang_masuk', ['periode' => 'hari']) }}" class="btn btn-outline-primary btn-sm mt-2">Tampilkan Detail</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Uang Masuk Minggu Ini</h6>
                    <h3 class="text-primary mb-0">{{ formatRupiah($totalMingguIni ?? 0) }}</h3>
                    <a href="{{ route('laporan.uang_masuk', ['periode' => 'minggu']) }}" class="btn btn-outline-primary btn-sm mt-2">Tampilkan Detail</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Uang Masuk Bulan Ini</h6>
                    <h3 class="text-warning mb-0">{{ formatRupiah($totalBulanIni ?? 0) }}</h3>
                    <a href="{{ route('laporan.uang_masuk', ['periode' => 'bulan']) }}" class="btn btn-outline-primary btn-sm mt-2">Tampilkan Detail</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <!-- Laporan Tagihan -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-file me-2"></i>Laporan Tagihan</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.tagihan') }}">
                        <div class="mb-3">
                            <label class="form-label">Status Tagihan</label>
                            <select name="status" class="form-select select2">
                                <option value="">Pilih Status</option>
                                <option value="lunas">Lunas</option>
                                <option value="baru">Baru</option>
                                <option value="angsur">Angsur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas" class="form-select select2">
                                <option value="">Pilih Kelas</option>
                                @for($i = 10; $i <= 12; $i++)
                                    <option value="{{ $i }}">Kelas {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select select2">
                                <option value="">Pilih Bulan</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-select select2">
                                <option value="">Pilih Tahun Pelajaran</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ (request('tahun_pelajaran_id', $tahunPelajarans->firstWhere('is_aktif', 1)?->id) == $tp->id ? 'selected' : '') }}>{{ $tp->nama }}{{ $tp->is_aktif ? ' (Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bx bx-search me-1"></i> Tampilkan Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Laporan Pembayaran -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-money me-2"></i>Laporan Pembayaran</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.rekap_pembayaran') }}">
                        <div class="mb-3">
                            <label class="form-label">Status Pembayaran</label>
                            <select name="status_pembayaran" class="form-select select2">
                                <option value="">Pilih Status Pembayaran</option>
                                <option value="sudah_dikonfirmasi">Sudah Dikonfirmasi</option>
                                <option value="belum_dikonfirmasi">Belum Dikonfirmasi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas" class="form-select select2">
                                <option value="">Pilih Kelas</option>
                                @for($i = 10; $i <= 12; $i++)
                                    <option value="{{ $i }}">Kelas {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select select2">
                                <option value="">Pilih Bulan</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-select select2">
                                <option value="">Pilih Tahun Pelajaran</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ (request('tahun_pelajaran_id', $tahunPelajarans->firstWhere('is_aktif', 1)?->id) == $tp->id ? 'selected' : '') }}>{{ $tp->nama }}{{ $tp->is_aktif ? ' (Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="bx bx-search me-1"></i> Tampilkan Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- @push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush --}}

@push('styles')
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        padding: 0.4375rem 0.875rem;
        font-size: 0.9375rem;
        border-radius: 0.375rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #566a7f;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 38px;
        right: 10px;
    }
    .card-header {
        font-weight: 600;
        font-size: 1.1rem;
    }
    .btn-primary, .btn-success {
        font-weight: 600;
        font-size: 1rem;
    }
</style>
@endpush

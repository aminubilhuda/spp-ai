@extends('layouts.app_sneat')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Laporan</h5>
            </div>
            <div class="card-body">
                <!-- Laporan Tagihan -->
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-4">
                        <h6 class="text-muted mb-0" style="width: 150px;">Laporan Tagihan</h6>
                        <div class="d-flex gap-2 flex-grow-1">
                            <select name="status" class="form-select" style="width: 180px;">
                                <option value="">Pilih Status</option>
                                <option value="lunas">Lunas</option>
                                <option value="baru">Baru</option>
                                <option value="angsur">Angsur</option>
                            </select>
                            <select name="kelas" class="form-select" style="width: 120px;">
                                <option value="">Pilih Kelas</option>
                                @for($i = 10; $i <= 12; $i++)
                                    <option value="{{ $i }}">Kelas {{ $i }}</option>
                                @endfor
                            </select>
                            <select name="bulan" class="form-select" style="width: 180px;">
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
                            <select name="tahun" class="form-select" style="width: 120px;">
                                @php
                                    $currentYear = date('Y');
                                    $startYear = $currentYear - 2;
                                @endphp
                                @for($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-primary" style="width: 120px;">
                                <i class="bx bx-search me-1"></i> Tampil
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Laporan Pembayaran -->
                <div>
                    <div class="d-flex align-items-center gap-4">
                        <h6 class="text-muted mb-0" style="width: 150px;">Laporan Pembayaran</h6>
                        <div class="d-flex gap-2 flex-grow-1">
                            <select name="status_pembayaran" class="form-select" style="width: 180px;">
                                <option value="">Pilih Status Pembayaran</option>
                                <option value="sudah_dikonfirmasi">Sudah Dikonfirmasi</option>
                                <option value="belum_dikonfirmasi">Belum Dikonfirmasi</option>
                            </select>
                            <select name="kelas" class="form-select" style="width: 120px;">
                                <option value="">Pilih Kelas</option>
                                @for($i = 10; $i <= 12; $i++)
                                    <option value="{{ $i }}">Kelas {{ $i }}</option>
                                @endfor
                            </select>
                            <select name="bulan" class="form-select" style="width: 180px;">
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
                            <select name="tahun" class="form-select" style="width: 120px;">
                                @php
                                    $currentYear = date('Y');
                                    $startYear = $currentYear - 2;
                                @endphp
                                @for($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-primary" style="width: 120px;">
                                <i class="bx bx-search me-1"></i> Tampil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Inisialisasi Select2 jika diperlukan
    $(document).ready(function() {
        $('.form-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endsection

@section('styles')
<style>
    .card {
        box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
    }

    .form-select {
        padding: 0.4375rem 2rem 0.4375rem 0.875rem;
        font-size: 0.9375rem;
        font-weight: 400;
        line-height: 1.5;
        color: #566a7f;
        background-color: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1);
        outline: 0;
    }

    .btn-primary {
        background-color: #696cff;
        border-color: #696cff;
    }

    .btn-primary:hover {
        background-color: #5f65f6;
        border-color: #5f65f6;
    }

    .text-muted {
        color: #697a8d !important;
    }

    .gap-4 {
        gap: 1.5rem !important;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }
</style>
@endsection

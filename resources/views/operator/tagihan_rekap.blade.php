@extends('layouts.app_sneat_blank')
@section('title', 'Rekap Tagihan Siswa')
@section('content')
    <style>
        .receipt-container {
            width: 210mm;
            /* Ukuran kertas A4 */
            margin: 20px auto;
            border: 2px solid #000;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .header-section {
            background-color: #f8f9fa;
            padding: 15px 25px;
            border-bottom: 1px solid #000;
        }

        .school-info {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 0;
        }

        .student-info {
            padding: 20px 25px;
            border-bottom: 1px solid #000;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
        }

        .tagihan-table {
            width: calc(100% - 40px);
            border-collapse: collapse;
            margin: 20px;
            font-size: 12px;
            table-layout: fixed;
        }

        .tagihan-table th,
        .tagihan-table td {
            border: 1px solid #000;
            padding: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tagihan-table th:nth-child(1),
        .tagihan-table td:nth-child(1) {
            width: 5%;
        }

        .tagihan-table th:nth-child(2),
        .tagihan-table td:nth-child(2) {
            width: 25%;
        }

        .tagihan-table th:nth-child(3),
        .tagihan-table td:nth-child(3) {
            width: 10%;
        }

        .tagihan-table th:nth-child(4),
        .tagihan-table td:nth-child(4),
        .tagihan-table th:nth-child(5),
        .tagihan-table td:nth-child(5),
        .tagihan-table th:nth-child(6),
        .tagihan-table td:nth-child(6) {
            width: 15%;
        }

        .tagihan-table th:nth-child(7),
        .tagihan-table td:nth-child(7) {
            width: 10%;
        }

        .tagihan-table th {
            background-color: #f8f9fa;
        }

        .total-section {
            margin: 20px;
            text-align: right;
            font-weight: bold;
        }

        .footer-section {
            display: flex;
            justify-content: flex-end;
            padding: 20px;
            font-size: 14px;
        }

        .signature {
            text-align: center;
        }

        .print-date {
            font-size: 12px;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>

    <div class="receipt-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="school-info">
                <div><strong>SMK ANTARA NUSA</strong></div>
                <div>Jl. Raya Bojong Soang No.12</div>
                <div>Telp. (022) 123456</div>
            </div>
            <h1 class="title">REKAP TAGIHAN SISWA</h1>
        </div>

        <!-- Student Info Section -->
        <div class="student-info">
            <div class="info-row">
                <span class="info-label">Nama Siswa</span>
                <span>: {{ $siswa->nama }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">NISN</span>
                <span>: {{ $siswa->nisn }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kelas</span>
                <span>: {{ $siswa->kelas }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jurusan</span>
                <span>: {{ $siswa->jurusan->nama }}</span>
            </div>
        </div>

        <!-- Tagihan Table -->
        <div style="margin: 0 20px;">
            <table class="tagihan-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Tagihan</th>
                        <th>Periode</th>
                        <th>Total Tagihan</th>
                        <th>Total Dibayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_tagihan = 0;
                        $total_dibayar = 0;
                        $total_sisa = 0;
                    @endphp
                    @foreach ($tagihan_details as $detail)
                        @php
                            $total_pembayaran = $detail->pembayaran
                                ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                ->sum('jumlah_dibayar');
                            $sisa = $detail->jumlah_biaya - $total_pembayaran;

                            $total_tagihan += $detail->jumlah_biaya;
                            $total_dibayar += $total_pembayaran;
                            $total_sisa += $sisa;

                            $tanggal = \Carbon\Carbon::parse($detail->tagihan->tanggal_tagihan);
                            $periode = $tanggal->format('M Y');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->nama_biaya }}</td>
                            <td>{{ $periode }}</td>
                            <td>Rp {{ number_format($detail->jumlah_biaya, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($total_pembayaran, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($detail->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td colspan="3" style="text-align: right;">Total:</td>
                        <td>Rp {{ number_format($total_tagihan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($total_dibayar, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($total_sisa, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="signature">
                <div class="print-date">{{ now()->translatedFormat('d F Y') }}</div>
                <div>Admin</div>
                <br><br><br>
                <div>{{ strtoupper(auth()->user()->name) }}</div>
            </div>
        </div>
    </div>
@endsection

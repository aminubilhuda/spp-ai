@extends('layouts.app_sneat_blank', ['title' => 'Bukti Pembayaran'])
@section('title', 'Bukti Pembayaran')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Bukti Pembayaran</h4>
                    <div>
                        <button onclick="window.print()" class="btn btn-secondary btn-sm">
                            <i class="bx bx-printer"></i> Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        @page {
            size: 105mm 330mm;
            margin: 5mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8pt;
            line-height: 1.3;
            width: 95mm;
            margin: 0;
            padding: 0;
        }

        .receipt-container {
            width: 100%;
            margin: 2mm auto;
            padding: 2mm;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5mm 0;
            position: relative;
            text-align: center;
        }

        .divider::after {
            content: "✂ gunting di sini";
            position: absolute;
            top: -7pt;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 5mm;
            font-size: 7pt;
            color: #666;
        }

        .copy-label {
            position: absolute;
            top: 2mm;
            right: 2mm;
            font-size: 7pt;
            font-weight: bold;
            color: #666;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 3mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
            position: relative;
        }

        .logo {
            position: absolute;
            left: 0;
            width: 12mm;
            height: 12mm;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .school-info {
            text-align: center;
            padding-left: 14mm;
            padding-right: 14mm;
            width: 100%;
        }

        .school-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin: 3mm 0;
            font-size: 10pt;
        }

        .payment-table {
            width: 100%;
            margin: 2mm 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            font-size: 8pt;
        }

        .payment-table th, .payment-table td {
            padding: 1mm 0.5mm;
        }

        .payment-table th {
            text-align: left;
            border-bottom: 1px solid #000;
        }

        @media print {
            .receipt-container {
                margin: 0;
                padding: 2mm;
            }
            .container-fluid {
                display: none;
            }
        }
    </style>

    <!-- Bagian Untuk Sekolah -->
    <div class="receipt-container">
        {{-- <div class="copy-label">ARSIP SEKOLAH</div> --}}
        <div class="header">
            @php
                $logoUrl = getInstansiLogoUrl();
            @endphp
            @if($logoUrl)
                <div class="logo">
                    <img src="{{ $logoUrl }}" alt="Logo Instansi">
                </div>
            @endif
            <div class="school-info">
                <div class="school-name">{{ strtoupper(getInstansiSetting('nama_instansi') ?: 'NAMA INSTANSI') }}</div>
                <div>{{ getInstansiSetting('alamat_instansi') ?: 'ALAMAT INSTANSI' }}</div>
            </div>
        </div>

        <div class="title">BUKTI PEMBAYARAN</div>

        <table class="info-table" style="width: 100%; font-size: 8pt; margin-bottom: 3mm;">
            <tr>
                <td style="width: 25%">No Transaksi</td>
                <td style="width: 35%">: {{ $pembayaran->id }}</td>
                <td style="width: 15%">Tanggal</td>
                <td style="width: 25%">: {{ $pembayaran->tanggal_bayar ? date('d-m-Y', strtotime($pembayaran->tanggal_bayar)) : '-' }}</td>
            </tr>
            <tr>
                <td>No Induk</td>
                <td>: {{ $pembayaran->tagihan->siswa->nisn }}</td>
                <td>Kelas</td>
                <td>: {{ $pembayaran->tagihan->siswa->kelas }}</td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>: {{ $pembayaran->tagihan->siswa->nama }}</td>
                <td>Metode</td>
                <td>: {{ $pembayaran->metode_pembayaran }}</td>
            </tr>
        </table>

        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Nama Pembayaran</th>
                    <th style="width: 25%">Periode</th>
                    <th style="width: 25%; text-align: right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $pembayaran->tagihan_detail->nama_biaya }}</td>
                    <td>
                        @if($pembayaran->tagihan->tanggal_tagihan)
                            @php
                                $bulan = \Carbon\Carbon::parse($pembayaran->tagihan->tanggal_tagihan)->format('m');
                                $tahun = \Carbon\Carbon::parse($pembayaran->tagihan->tanggal_tagihan)->format('Y');
                                $namaBulan = [
                                    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                                    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                                    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
                                ];
                            @endphp
                            {{ $namaBulan[$bulan] }} {{ $tahun }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right">{{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table style="width: 100%; font-size: 8pt;">
            <tr>
                <td style="width: 60%"></td>
                <td style="width: 40%; text-align: right;">
                    <table style="width: 100%">
                        <tr>
                            <td style="text-align: left">Total</td>
                            <td>:</td>
                            <td style="text-align: right"> {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left">{{ $pembayaran->metode_pembayaran }}</td>
                            <td>:</td>
                            <td style="text-align: right"> {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left">Kembali</td>
                            <td>:</td>
                            <td style="text-align: right"> 0</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right; padding-top: 3mm;">
                    {{ getInstansiSetting('nama_instansi') }}, {{ date('d-m-Y') }}<br>
                    Petugas<br><br><br>
                    {{ auth()->user()->name }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Garis Pemisah -->
    <div class="divider"></div>

    <!-- Bagian Untuk Siswa -->
    <div class="receipt-container">
        {{-- <div class="copy-label">ARSIP SISWA</div> --}}
        <div class="header">
            @if($logoUrl)
                <div class="logo">
                    <img src="{{ $logoUrl }}" alt="Logo Instansi">
                </div>
            @endif
            <div class="school-info">
                <div class="school-name">{{ strtoupper(getInstansiSetting('nama_instansi') ?: 'NAMA INSTANSI') }}</div>
                <div>{{ getInstansiSetting('alamat_instansi') ?: 'ALAMAT INSTANSI' }}</div>
            </div>
        </div>

        <div class="title">BUKTI PEMBAYARAN</div>

        <table class="info-table" style="width: 100%; font-size: 8pt; margin-bottom: 3mm;">
            <tr>
                <td style="width: 25%">No Transaksi</td>
                <td style="width: 35%">: {{ $pembayaran->id }}</td>
                <td style="width: 15%">Tanggal</td>
                <td style="width: 25%">: {{ $pembayaran->tanggal_bayar ? date('d-m-Y', strtotime($pembayaran->tanggal_bayar)) : '-' }}</td>
            </tr>
            <tr>
                <td>No Induk</td>
                <td>: {{ $pembayaran->tagihan->siswa->nisn }}</td>
                <td>Kelas</td>
                <td>: {{ $pembayaran->tagihan->siswa->kelas }}</td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>: {{ $pembayaran->tagihan->siswa->nama }}</td>
                <td>Metode</td>
                <td>: {{ $pembayaran->metode_pembayaran }}</td>
            </tr>
        </table>

        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Nama Pembayaran</th>
                    <th style="width: 25%">Periode</th>
                    <th style="width: 25%; text-align: right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $pembayaran->tagihan_detail->nama_biaya }}</td>
                    <td>
                        @if($pembayaran->tagihan->tanggal_tagihan)
                            @php
                                $bulan = \Carbon\Carbon::parse($pembayaran->tagihan->tanggal_tagihan)->format('m');
                                $tahun = \Carbon\Carbon::parse($pembayaran->tagihan->tanggal_tagihan)->format('Y');
                                $namaBulan = [
                                    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                                    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                                    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
                                ];
                            @endphp
                            {{ $namaBulan[$bulan] }} {{ $tahun }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right">{{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table style="width: 100%; font-size: 8pt;">
            <tr>
                <td style="width: 60%"></td>
                <td style="width: 40%; text-align: right;">
                    <table style="width: 100%">
                        <tr>
                            <td style="text-align: left">Total</td>
                            <td>:</td>
                            <td style="text-align: right"> {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left">{{ $pembayaran->metode_pembayaran }}</td>
                            <td>:</td>
                            <td style="text-align: right"> {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left">Kembali</td>
                            <td>:</td>
                            <td style="text-align: right">: 0</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right; padding-top: 3mm;">
                    {{ getInstansiSetting('nama_instansi') }}, {{ date('d-m-Y') }}<br>
                    Petugas<br><br><br>
                    {{ auth()->user()->name }}
                </td>
            </tr>
        </table>
    </div>
@endsection

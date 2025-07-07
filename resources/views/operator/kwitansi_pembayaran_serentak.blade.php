@extends('layouts.app_sneat_blank', ['title' => 'Bukti Pembayaran Serentak'])
@section('title', 'Bukti Pembayaran Serentak')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Bukti Pembayaran Serentak</h4>
                    <div>
                        <form method="GET" action="{{ route('kwitansi.showBatch.pdf') }}" target="_blank" style="display: inline;">
                            @foreach($pembayaranIds as $id)
                                <input type="hidden" name="pembayaran_ids[]" value="{{ $id }}">
                            @endforeach
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bx bx-download"></i> Download PDF
                            </button>
                        </form>
                        <button onclick="window.print()" class="btn btn-secondary btn-sm">
                            <i class="bx bx-printer"></i> Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.5;
        }

        .receipt-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            position: relative;
        }

        .logo {
            position: absolute;
            left: 0;
            width: 60px;
            height: 60px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .school-info {
            text-align: center;
            padding-left: 70px;
            padding-right: 70px;
            width: 100%;
        }

        .school-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            font-size: 14px;
        }

        .transaction-info {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            gap: 10px;
            margin-bottom: 20px;
        }

        .payment-table {
            width: 100%;
            margin: 20px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .payment-table th, .payment-table td {
            padding: 8px 4px;
        }

        .payment-table th {
            text-align: left;
        }

        .total-section {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin: 20px 0;
        }

        .signature {
            text-align: right;
            margin-top: 30px;
        }

        @media print {
            .receipt-container {
                margin: 0;
                padding: 10px;
            }
            .container-fluid {
                display: none;
            }
        }
    </style>

    <div class="receipt-container">
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

        <div class="title">BUKTI PEMBAYARAN SERENTAK</div>

        <div class="transaction-info">
            <div>No Transaksi</div>
            <div>: {{ $pembayaranIds[0] ?? 'BATCH-' . date('YmdHis') }}</div>
            <div>Tanggal</div>
            <div>: {{ $pembayaran->tanggal_bayar ? date('d-m-Y H:i:s', strtotime($pembayaran->tanggal_bayar)) : '-' }}</div>
            
            <div>No Induk</div>
            <div>: {{ $pembayaran->tagihan->siswa->nisn }}</div>
            <div>Kelas</div>
            <div>: {{ $pembayaran->tagihan->siswa->kelas }}</div>
            
            <div>Nama</div>
            <div>: {{ $pembayaran->tagihan->siswa->nama }}</div>
            <div>Metode</div>
            <div>: {{ $pembayaran->metode_pembayaran }}</div>
        </div>

        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Nama Pembayaran</th>
                    <th style="width: 15%">Periode</th>
                    <th style="width: 25%; text-align: right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; $totalBayar = 0; @endphp
                @foreach($pembayaranList as $pembayaranItem)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $pembayaranItem->tagihan_detail->nama_biaya }}</td>
                        <td>
                            @if($pembayaranItem->tagihan->tanggal_tagihan)
                                @php
                                    $bulan = \Carbon\Carbon::parse($pembayaranItem->tagihan->tanggal_tagihan)->format('m');
                                    $tahun = \Carbon\Carbon::parse($pembayaranItem->tagihan->tanggal_tagihan)->format('Y');
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
                        <td style="text-align: right">{{ number_format($pembayaranItem->jumlah_dibayar, 0, ',', '.') }}</td>
                    </tr>
                    @php $totalBayar += $pembayaranItem->jumlah_dibayar; @endphp
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div></div>
            <div>
                <div>Total : {{ number_format($totalBayar, 0, ',', '.') }}</div>
                <div>{{ $pembayaran->metode_pembayaran }} : {{ number_format($totalBayar, 0, ',', '.') }}</div>
                <div>Kembali : 0</div>
            </div>
        </div>

        <div class="signature">
            <div>{{ getInstansiSetting('nama_instansi') }}, {{ date('d-m-Y') }}</div>
            <div>Petugas</div>
            <br><br><br>
            <div>{{ auth()->user()->name }}</div>
        </div>
    </div>
@endsection 
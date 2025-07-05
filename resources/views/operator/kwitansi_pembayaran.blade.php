@extends('layouts.app_sneat_blank', ['title' => 'Bukti Pembayaran'])
@section('title', 'Bukti Pembayaran')
@section('content')
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
        }
    </style>

    <div class="receipt-container">
        <div class="header">
            @if(getInstansiSetting('logo_instansi'))
                <div class="logo">
                    <img src="{{ Storage::disk('public')->url(getInstansiSetting('logo_instansi')) }}" alt="Logo Instansi">
                </div>
            @endif
            <div class="school-info">
                <div class="school-name">{{ strtoupper(getInstansiSetting('nama_instansi') ?: 'NAMA INSTANSI') }}</div>
                <div>{{ getInstansiSetting('alamat_instansi') ?: 'ALAMAT INSTANSI' }}</div>
            </div>
        </div>

        <div class="title">BUKTI PEMBAYARAN</div>

        <div class="transaction-info">
            <div>No Transaksi</div>
            <div>: {{ $pembayaran->id }}</div>
            <div>Tanggal</div>
            <div>: {{ $pembayaran->tanggal_bayar ? date('d-m-Y H:i:s', strtotime($pembayaran->tanggal_bayar)) : '-' }}</div>
            
            <div>No Induk</div>
            <div>: {{ $pembayaran->tagihan->siswa->nisn }}</div>
            <div>Kelas</div>
            <div>: {{ $pembayaran->tagihan->siswa->kelas }}</div>
            
            <div>Nama</div>
            <div>: {{ $pembayaran->tagihan->siswa->nama }}</div>
            <div></div>
            <div></div>
        </div>

        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Nama Pembayaran</th>
                    <th style="width: 25%; text-align: right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $pembayaran->tagihan_detail->nama_biaya }}</td>
                    <td style="text-align: right">{{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <div></div>
            <div>
                <div>Total : {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</div>
                <div>Tunai : {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</div>
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

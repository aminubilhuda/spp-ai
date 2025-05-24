@extends('layouts.app_sneat_blank')
@section('title', 'Kwitansi Pembayaran')
@section('content')

    <style>
        .receipt-container {
            max-width: 800px;
            margin: 50px auto;
            border: 2px solid #000;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        .header-section {
            background-color: #f8f9fa;
            padding: 15px;
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
        
        .payment-details {
            padding: 20px;
        }
        
        .form-row {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .form-label {
            width: 120px;
            font-weight: normal;
            margin: 0;
            font-size: 14px;
        }
        
        .form-value {
            flex: 1;
            background-color: #e9ecef;
            padding: 5px 10px;
            border: 1px solid #000;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .amount-section {
            border: 1px solid #000;
            padding: 15px;
            margin: 20px 0;
        }
        
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .amount-label {
            font-weight: normal;
        }
        
        .amount-value {
            background-color: #e9ecef;
            padding: 3px 8px;
            border: 1px solid #000;
            min-width: 150px;
            text-align: right;
        }
        
        .payment-for {
            border: 1px solid #000;
            padding: 15px;
            margin: 20px 0;
        }
        
        .payment-for-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .payment-for-content {
            background-color: #e9ecef;
            padding: 8px;
            border: 1px solid #000;
            font-size: 12px;
        }
        
        .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding: 0 20px 20px 20px;
        }
        
        .date {
            font-size: 14px;
        }
        
        .admin-signature {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .two-column {
            display: flex;
            gap: 20px;
        }
        
        .left-column {
            flex: 1;
        }
        
        .right-column {
            flex: 1;
        }
    </style>

    <div class="container-fluid">
        <div class="receipt-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="school-info">
                    <div><strong>SMK ANTARA NUSA</strong></div>
                    <div>Jl. Raya Bojong Soang No.12</div>
                    <div>Telp. (022) 123456</div>
                </div>
                <h1 class="title">KWITANSI PEMBAYARAN</h1>
            </div>
            
            <!-- Payment Details -->
            <div class="payment-details">
                <div class="two-column">
                    <div class="left-column">
                        <div class="form-row">
                            <label class="form-label">ID Bayar</label>
                            <div class="form-value">SMKAN-{{ $pembayaran->id }}</div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Nama Siswa</label>
                            <div class="form-value">{{ $pembayaran->tagihan->siswa->nama }}</div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Kelas</label>
                            <div class="form-value">{{ $pembayaran->tagihan->siswa->kelas }}</div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">NISN</label>
                            <div class="form-value">{{ $pembayaran->tagihan->siswa->nisn }}</div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Jumlah Bayar</label>
                            <div class="form-value">Rp {{ number_format($pembayaran->jumlah_dibayar, 0, ',', '.') }}</div>
                        </div>                        <div class="form-row">
                            <label class="form-label">Status</label>
                            <div class="form-value">{{ ucfirst($pembayaran->tagihan_detail->status ?? 'belum lunas') }}</div>
                        </div>
                    </div>
                    
                    <div class="right-column">
                        <div class="form-row"><label class="form-label">Tanggal Bayar</label>                            
                            <div class="form-value">{{ $pembayaran->tanggal_bayar ? date('d/m/Y', strtotime($pembayaran->tanggal_bayar)) : '-' }}</div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Nomor Cetak</label>
                            <div class="form-value">{{ $nomor_cetak }}</div>
                        </div>
                        
                        <!-- Amount Section -->
                        <div class="amount-section">
                            <div class="amount-row">
                                <span class="amount-label">Jumlah Tagihan</span>
                                <div class="amount-value">Rp {{ number_format($total_tagihan, 0, ',', '.') }}</div>
                            </div>
                            <div class="amount-row">
                                <span class="amount-label">Total Sudah Dibayar</span>
                                <div class="amount-value">Rp {{ number_format($total_sudah_bayar, 0, ',', '.') }}</div>
                            </div>
                            <div class="amount-row">
                                <span class="amount-label">Sisa Bayar</span>
                                <div class="amount-value">Rp {{ number_format($sisa_bayar, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment For Section -->
                <div class="payment-for">
                    <div class="payment-for-title">Pembayaran Untuk:</div>
                    <div class="payment-for-content">
                        {{ $pembayaran->tagihan_detail->nama_biaya }}
                    </div>
                </div>
            </div>
            
            <!-- Footer Section -->
            <div class="footer-section">                <div class="date">{{ now()->format('d F Y') }}</div>
                <div class="admin-signature">{{ strtoupper(auth()->user()->name) }}</div>
            </div>
        </div>
    </div>
@endsection
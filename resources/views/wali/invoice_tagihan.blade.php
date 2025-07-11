<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan - {{ $invoiceId }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(request('html') == 'true' || request()->is('*/html'))
        <link href="{{ asset('css/invoice.css') }}" rel="stylesheet">
    @else
        <style>
            /* CSS untuk PDF - DomPDF tidak mendukung CSS eksternal */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                line-height: 1.4;
                color: #333;
                background: #f9fafb;
                margin: 0;
                padding: 20px;
                font-size: 12px;
            }

            .invoice-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }

            .invoice-header {
                background: #374151;
                color: white;
                padding: 30px;
                position: relative;
            }

            .header-content {
                display: table;
                width: 100%;
            }

            .header-left {
                display: table-cell;
                vertical-align: top;
                width: 60%;
            }

            .header-right {
                display: table-cell;
                vertical-align: top;
                width: 40%;
                text-align: right;
            }

            .header-left h1 {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .header-left .invoice-meta {
                font-size: 12px;
                opacity: 0.9;
                line-height: 1.4;
            }

            .logo-container {
                margin-bottom: 15px;
            }

            .logo-container img {
                max-height: 60px;
                width: auto;
                border-radius: 4px;
                background: rgba(255, 255, 255, 0.1);
                padding: 5px;
            }

            .school-info h2 {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 8px;
            }

            .school-info p {
                font-size: 11px;
                opacity: 0.9;
                margin-bottom: 3px;
            }

            .invoice-body {
                padding: 30px;
            }

            .info-grid {
                display: table;
                width: 100%;
                margin-bottom: 30px;
            }

            .info-section {
                display: table-cell;
                width: 50%;
                vertical-align: top;
                padding-right: 20px;
            }

            .info-section:last-child {
                padding-right: 0;
                padding-left: 20px;
            }

            .info-section h3 {
                font-size: 14px;
                font-weight: bold;
                color: #374151;
                margin-bottom: 12px;
                text-transform: uppercase;
                border-bottom: 2px solid #e5e7eb;
                padding-bottom: 5px;
            }

            .info-section .info-item {
                margin-bottom: 8px;
            }

            .info-section .info-label {
                font-size: 11px;
                color: #6b7280;
                margin-bottom: 2px;
                font-weight: bold;
            }

            .info-section .info-value {
                font-size: 12px;
                font-weight: normal;
                color: #1f2937;
            }

            .invoice-table {
                width: 100%;
                border-collapse: collapse;
                margin: 25px 0;
                background: white;
                border: 1px solid #e5e7eb;
            }

            .invoice-table th {
                background: #f8fafc;
                color: #374151;
                font-weight: bold;
                padding: 12px 8px;
                text-align: left;
                font-size: 11px;
                text-transform: uppercase;
                border-bottom: 2px solid #e5e7eb;
            }

            .invoice-table td {
                padding: 10px 8px;
                border-bottom: 1px solid #f3f4f6;
                color: #374151;
                font-size: 11px;
            }

            .invoice-table tr:last-child td {
                border-bottom: none;
            }

            .invoice-table .total-row {
                background: #f8fafc;
                font-weight: bold;
            }

            .invoice-table .total-row td {
                color: #1f2937;
                font-size: 12px;
            }

            .text-right {
                text-align: right;
            }

            .badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 12px;
                font-size: 9px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .badge-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #10b981;
            }

            .badge-warning {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #f59e0b;
            }

            .badge-info {
                background: #dbeafe;
                color: #1e40af;
                border: 1px solid #3b82f6;
            }

            .signature-section {
                margin-top: 40px;
                text-align: right;
                padding-top: 20px;
                border-top: 1px solid #e5e7eb;
            }

            .signature-section .signature-info {
                display: inline-block;
                text-align: left;
            }

            .signature-section .date {
                font-size: 11px;
                color: #6b7280;
                margin-bottom: 5px;
            }

            .signature-section .title {
                font-size: 12px;
                font-weight: bold;
                color: #374151;
                margin-bottom: 30px;
            }

            .signature-section .signature-line {
                width: 150px;
                border-bottom: 2px solid #374151;
                margin-bottom: 8px;
            }

            .signature-section .name {
                font-size: 12px;
                font-weight: bold;
                color: #1f2937;
                margin-bottom: 2px;
            }

            .signature-section .nip {
                font-size: 10px;
                color: #6b7280;
            }

            .button-group {
                margin-top: 30px;
                text-align: center;
                padding: 15px;
                background: #f8fafc;
                border-top: 1px solid #e5e7eb;
            }

            .btn {
                display: inline-block;
                padding: 8px 16px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                font-size: 11px;
                margin: 0 5px;
                border: 1px solid #d1d5db;
            }

            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }

            .btn-primary {
                background: #3b82f6;
                color: white;
                border-color: #2563eb;
            }

            /* Print styles untuk PDF */
            @media print {
                body {
                    background: white;
                    padding: 0;
                }
                
                .invoice-container {
                    border: none;
                    border-radius: 0;
                }

                .button-group {
                    display: none;
                }

                .invoice-header {
                    background: #374151 !important;
                }
            }
        </style>
    @endif
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="header-left">
                    <h1>TAGIHAN</h1>
                    <div class="invoice-meta">
                        <div>{{ $invoiceId }}</div>
                        @php
                            setlocale(LC_TIME, 'id_ID');
                            \Carbon\Carbon::setLocale('id');
                        @endphp
                        <div>Tuban, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y') }}</div>
                    </div>
                </div>
                <div class="header-right">
                    @php
                        $logoUrl = getInstansiLogoUrl();
                    @endphp
                    @if($logoUrl)
                        <div class="logo-container">
                            <img src="{{ $logoUrl }}" alt="Logo Sekolah">
                        </div>
                    @endif
                    <div class="school-info">
                        <h2>{{ getInstansiSetting('nama_instansi') }}</h2>
                        <p>{{ getInstansiSetting('alamat_instansi') }}</p>
                        <p>Surel: {{ getInstansiSetting('email_instansi') }}</p>
                        <p>Telp: {{ getInstansiSetting('nomor_wa_instansi') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">
            <!-- Info Sections -->
            <div class="info-grid">
                <div class="info-section">
                    <h3>Data Siswa</h3>
                    <div class="info-item">
                        <div class="info-label">Nama</div>
                        <div class="info-value">{{ $tagihan->siswa->nama }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NISN</div>
                        <div class="info-value">{{ $tagihan->siswa->nisn }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIS</div>
                        <div class="info-value">{{ $tagihan->siswa->nis }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">{{ $tagihan->siswa->kelas }} - {{ $tagihan->siswa->jurusan->nama }}</div>
                    </div>
                </div>
                <div class="info-section">
                    <h3>Wali Murid</h3>
                    <div class="info-item">
                        <div class="info-label">Nama</div>
                        <div class="info-value">{{ $tagihan->siswa->wali->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $tagihan->siswa->wali->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. HP</div>
                        <div class="info-value">{{ $tagihan->siswa->wali->nohp }}</div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Tagihan</th>
                        <th>Item Tagihan</th>
                        <th class="text-right">Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $total = 0;
                        $no = 1;
                    @endphp
                    @foreach($semuaTagihan as $tagihan)
                        @foreach($tagihan->tagihan_details as $detail)
                            @php
                                $total += $detail->jumlah_biaya;
                                $totalDibayar = $detail->pembayaran()
                                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                    ->sum('jumlah_dibayar');
                                $sisaBayar = $detail->jumlah_biaya - $totalDibayar;
                                
                                if ($sisaBayar <= 0) {
                                    $statusClass = 'success';
                                    $statusText = 'Lunas';
                                } elseif ($totalDibayar > 0) {
                                    $statusClass = 'info';
                                    $statusText = 'Sebagian';
                                } else {
                                    $statusClass = 'warning';
                                    $statusText = 'Belum Lunas';
                                }
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->locale('id')->isoFormat('D MMMM Y') }}</td>
                                <td>{{ $detail->nama_biaya }}</td>
                                <td class="text-right">{{ formatRupiah($detail->jumlah_biaya) }}</td>
                                <td><span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span></td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">Total:</td>
                        <td class="text-right">{{ formatRupiah($total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Signature -->
            <div class="signature-section">
                <div class="signature-info">
                    @php
                        setlocale(LC_TIME, 'id_ID');
                        \Carbon\Carbon::setLocale('id');
                    @endphp
                    <div class="date">Tuban, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y') }}</div>
                    <div class="title">{{ getInstansiSetting('nama_jabatan') ?: 'Bendahara' }}</div>
                    @if(getInstansiTtdUrl())
                        <img src="{{ getInstansiTtdUrl() }}" alt="Tanda Tangan" style="max-height: 50px; margin: 10px 0;">
                    @else
                        <div class="signature-line"></div>
                    @endif
                    <div class="name">{{ getInstansiSetting('nama_penanggung_jawab') ?? '.........................' }}</div>
                    <div class="nip">{{ getInstansiSetting('nama_jabatan') ?: 'Bendahara' }}</div>
                </div>
            </div>
        </div>

        @if(request('html') == 'true')
        <!-- Buttons -->
        <div class="button-group">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bx bx-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['download' => 'true']) }}" class="btn btn-primary">
                <i class="bx bx-download"></i>
                <span>Unduh PDF</span>
            </a>
        </div>
        @endif
    </div>
</body>
</html> 
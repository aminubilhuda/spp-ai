<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Tagihan Siswa - {{ getInstansiSetting('nama_instansi') }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }
        .receipt-container {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .receipt-section {
            border: 1px solid #000;
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }
        .divider {
            border: 1px dashed #000;
            margin: 8mm 0;
            position: relative;
            text-align: center;
        }
        .scissors {
            position: relative;
            top: -8px;
            background: white;
            padding: 0 8px;
            font-size: 10px;
            color: #666;
        }
        .header-section {
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-bottom: 1px solid #000;
        }
        .copy-label {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 9px;
            font-weight: bold;
            color: #666;
        }
        .school-info {
            margin-bottom: 5px;
            font-size: 10px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }
        .student-info {
            padding: 8px 15px;
            border-bottom: 1px solid #000;
        }
        .info-row {
            margin-bottom: 3px;
            font-size: 10px;
        }
        .info-label {
            display: inline-block;
            width: 100px;
            font-weight: bold;
        }
        .tagihan-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }
        .tagihan-table th,
        .tagihan-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9px;
        }
        .tagihan-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .footer-section {
            padding: 8px 15px;
            text-align: right;
        }
        .signature {
            display: inline-block;
            text-align: center;
            font-size: 10px;
        }
        .print-date {
            margin-bottom: 5px;
            font-size: 9px;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Optimasi kolom tabel */
        .tagihan-table th:nth-child(1),
        .tagihan-table td:nth-child(1) {
            width: 5%;
        }
        .tagihan-table th:nth-child(2),
        .tagihan-table td:nth-child(2) {
            width: 40%;
        }
        .tagihan-table th:nth-child(3),
        .tagihan-table td:nth-child(3) {
            width: 15%;
        }
        .tagihan-table th:nth-child(4),
        .tagihan-table td:nth-child(4) {
            width: 25%;
        }
        .tagihan-table th:nth-child(5),
        .tagihan-table td:nth-child(5) {
            width: 15%;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Bagian Atas (Untuk Sekolah) -->
        <div class="receipt-section">
            <div style="position: relative;">
                <div class="copy-label">ARSIP SEKOLAH</div>
                <!-- Header Section -->
                <div class="header-section">
                    <div class="school-info">
                        <div><strong>{{ strtoupper(getInstansiSetting('nama_instansi')) }}</strong></div>
                        <div>{{ getInstansiSetting('alamat_instansi') }}</div>
                        <div>Telp. {{ getInstansiSetting('nomor_wa_instansi') }}</div>
                        <div>Email: {{ getInstansiSetting('email_instansi') }}</div>
                    </div>
                    <div class="title">REKAP TAGIHAN SISWA</div>
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
                <div style="padding: 0 25px;">
                    <table class="tagihan-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Tagihan</th>
                                <th>Periode</th>
                                <th>Total Tagihan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_tagihan = 0;
                            @endphp
                            @foreach ($tagihan_details as $detail)
                                @php
                                    $total_tagihan += $detail->jumlah_biaya;
                                    $tanggal = \Carbon\Carbon::parse($detail->tagihan->tanggal_tagihan);
                                    $periode = $tanggal->format('M Y');
                                @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                                    <td>{{ $detail->nama_biaya }}</td>
                                    <td style="text-align: center;">{{ $periode }}</td>
                                    <td style="text-align: right;">Rp {{ number_format($detail->jumlah_biaya, 0, ',', '.') }}</td>
                                    <td style="text-align: center;">{{ $detail->status_detail }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Total:</td>
                                <td style="text-align: right;">Rp {{ number_format($total_tagihan, 0, ',', '.') }}</td>
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
        </div>

        <!-- Garis Pemisah -->
        <div class="divider">
            <span class="scissors">✂ Potong di sini</span>
        </div>

        <!-- Bagian Bawah (Untuk Siswa) -->
        <div class="receipt-section">
            <div style="position: relative;">
                <div class="copy-label">ARSIP SISWA</div>
                <!-- Header Section -->
                <div class="header-section">
                    <div class="school-info">
                        <div><strong>{{ strtoupper(getInstansiSetting('nama_instansi')) }}</strong></div>
                        <div>{{ getInstansiSetting('alamat_instansi') }}</div>
                        <div>Telp. {{ getInstansiSetting('nomor_wa_instansi') }}</div>
                        <div>Email: {{ getInstansiSetting('email_instansi') }}</div>
                    </div>
                    <div class="title">REKAP TAGIHAN SISWA</div>
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
                <div style="padding: 0 25px;">
                    <table class="tagihan-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Tagihan</th>
                                <th>Periode</th>
                                <th>Total Tagihan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_tagihan = 0;
                            @endphp
                            @foreach ($tagihan_details as $detail)
                                @php
                                    $total_tagihan += $detail->jumlah_biaya;
                                    $tanggal = \Carbon\Carbon::parse($detail->tagihan->tanggal_tagihan);
                                    $periode = $tanggal->format('M Y');
                                @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                                    <td>{{ $detail->nama_biaya }}</td>
                                    <td style="text-align: center;">{{ $periode }}</td>
                                    <td style="text-align: right;">Rp {{ number_format($detail->jumlah_biaya, 0, ',', '.') }}</td>
                                    <td style="text-align: center;">{{ $detail->status_detail }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Total:</td>
                                <td style="text-align: right;">Rp {{ number_format($total_tagihan, 0, ',', '.') }}</td>
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
        </div>
    </div>
</body>
</html>

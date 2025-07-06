<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan - {{ $invoiceId }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            line-height: 1.6;
            color: #2D3748;
            margin: 0;
            padding: 20px;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Nunito', sans-serif;
            color: #1A202C;
        }

        .invoice-container {
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }

        .invoice-header {
            margin-bottom: 30px;
        }

        .invoice-header .left h1 {
            font-weight: 700;
            letter-spacing: 1px;
            color: #2D3748;
        }

        .invoice-header .left p {
            color: #4A5568;
            font-size: 14px;
        }

        .invoice-header .right {
            float: right;
            width: 40%;
            text-align: right;
        }

        .invoice-header .right img {
            max-height: 70px;
            width: auto;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .invoice-header .right h2 {
            font-weight: 600;
            color: #2D3748;
        }

        .invoice-header .right p {
            color: #4A5568;
            font-size: 14px;
        }

        .info-section h3 {
            font-weight: 600;
            font-size: 16px;
            color: #2D3748;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }

        .info-section p {
            color: #4A5568;
            margin: 0 0 8px 0;
            font-size: 14px;
        }

        .info-section strong {
            font-weight: 600;
            color: #2D3748;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 14px;
        }

        .invoice-table th {
            background: #F7FAFC;
            color: #2D3748;
            font-weight: 600;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #E2E8F0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .invoice-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #E2E8F0;
            color: #4A5568;
        }

        .invoice-table .total-row {
            font-weight: 600;
            color: #2D3748;
            background: #F7FAFC;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .text-right {
            text-align: right;
        }

        .signature-section {
            margin-top: 50px;
            text-align: left;
            color: #2D3748;
            font-size: 14px;
        }

        .signature-section .signature-line {
            width: 150px;
            border-bottom: 1px solid #4A5568;
            margin: 80px 0 10px;
        }

        .signature-section p {
            margin: 0;
            line-height: 1.6;
        }

        /* Button Styles */
        .button-group {
            margin-top: 30px;
            text-align: right;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-left: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }

        .btn i {
            margin-right: 8px;
            font-size: 18px;
        }

        .btn-secondary {
            background: #EDF2F7;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        .btn-secondary:hover {
            background: #E2E8F0;
            color: #1A202C;
        }

        .btn-primary {
            background: #4299E1;
            color: white;
            box-shadow: 0 1px 3px 0 rgba(66, 153, 225, 0.1), 0 1px 2px 0 rgba(66, 153, 225, 0.06);
        }

        .btn-primary:hover {
            background: #3182CE;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(66, 153, 225, 0.1), 0 2px 4px -1px rgba(66, 153, 225, 0.06);
        }

        @media print {
            body {
                padding: 0;
            }
            
            .button-group {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="left">
                <h1 style="margin: 0; font-size: 28px;">TAGIHAN</h1>
                <p style="margin: 5px 0;">{{ $invoiceId }}</p>
                @php
                setlocale(LC_TIME, 'id_ID');
                \Carbon\Carbon::setLocale('id');
            @endphp
            <p style="margin: 5px 0;">Tuban, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y') }}</p>
            </div>
            <div class="right">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . str_replace('storage/', '', getInstansiSetting('logo_instansi'))))) }}" 
                    alt="Logo Sekolah" 
                    style="max-height: 70px; width: auto; object-fit: contain;"
                >
                <h2 style="margin: 5px 0; font-size: 18px;">{{ getInstansiSetting('nama_instansi') }}</h2>
                <p style="margin: 2px 0;">{{ getInstansiSetting('alamat_instansi') }}</p>
                <p style="margin: 2px 0;">Surel: {{ getInstansiSetting('email_instansi') }}</p>
                <p style="margin: 2px 0;">Telp: {{ getInstansiSetting('nomor_wa_instansi') }}</p>
            </div>
            <div class="clear"></div>
        </div>

        <div class="divider"></div>

        <!-- Info Sections -->
        <div class="info-sections">
            <div class="info-section">
                <h3>Data Siswa</h3>
                <p><strong>{{ $tagihan->siswa->nama }}</strong></p>
                <p>NISN: {{ $tagihan->siswa->nisn }}</p>
                <p>NIS: {{ $tagihan->siswa->nis }}</p>
                <p>Kelas: {{ $tagihan->siswa->kelas }} - {{ $tagihan->siswa->jurusan->nama }}</p>
            </div>
            <div class="info-section right">
                <h3>Wali Murid</h3>
                <p><strong>{{ $tagihan->siswa->wali->name }}</strong></p>
                <p>{{ $tagihan->siswa->wali->email }}</p>
                <p>{{ $tagihan->siswa->wali->nohp }}</p>
            </div>
            <div class="clear"></div>
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
                            <td>{{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->translatedFormat('d/m/Y') }}</td>
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
            @php
                setlocale(LC_TIME, 'id_ID');
                \Carbon\Carbon::setLocale('id');
            @endphp
            <p>Tuban, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y') }}</p>
            <p>Bendahara</p>
            <div class="signature-line"></div>
            <p>{{ getInstansiSetting('nama_bendahara') ?? '.........................' }}</p>
            <p>NIP. {{ getInstansiSetting('nip_bendahara') ?? '.........................' }}</p>
        </div>

        @if(!request('output'))
        <!-- Buttons -->
        <div class="button-group">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bx bx-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['output' => 'pdf']) }}" class="btn btn-primary">
                <i class="bx bx-download"></i>
                <span>Unduh PDF</span>
            </a>
        </div>
        @endif
    </div>
</body>
</html> 
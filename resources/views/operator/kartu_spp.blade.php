<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu SPP - {{ getInstansiSetting('nama_instansi') }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 20px;
            position: relative;
            min-height: 60px;
        }
        .school-logo {
            width: 45px;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .school-address {
            font-size: 11px;
        }
        .student-info {
            clear: both;
            margin: 20px 0;
        }
        .info-row {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .amount {
            text-align: right;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
            padding-right: 50px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid black;
            width: 200px;
            display: inline-block;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60px; vertical-align: top; border: none;">
                    <img src="{{ getInstansiLogoUrl() }}" alt="Logo Sekolah" class="school-logo">
                </td>
                <td style="vertical-align: top; border: none;">
                    <div class="school-name">{{ strtoupper(getInstansiSetting('nama_instansi')) }}</div>
                    <div class="school-address">{{ getInstansiSetting('alamat_instansi') }}</div>
                    <div class="school-address">{{ getInstansiSetting('email_instansi') }}</div>
                </td>
            </tr>
        </table>
    </div>
    {{-- <div class="clear"></div> --}}

    <div class="student-info">
        <div class="info-row">Nama Siswa: {{ $siswa->nama }} ({{ $siswa->nisn }})</div>
        <div class="info-row">Kelas: {{ $siswa->kelas }}</div>
        <div class="info-row">Jurusan: {{ $siswa->jurusan->nama }}</div>
    </div>

    <table>
        <thead>
            <tr style="border: 1px solid black;">
                <th style="width: 5%;">No</th>
                <th style="width: 20%">Bulan</th>
                <th style="width: 15%">Jumlah Tagihan</th>
                <th style="width: 13%">Tanggal Bayar</th>
                <th style="width: 15%">Paraf</th>
                <th style="width: 25%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tahun = Carbon\Carbon::now()->year;
                $bulan_sekarang = Carbon\Carbon::now()->month;
                
                // Jika bulan sekarang >= 7 (Juli), gunakan tahun sekarang, jika tidak tahun sebelumnya
                $tahun_awal = $bulan_sekarang >= 7 ? $tahun : $tahun - 1;
                $no = 1;
            @endphp

            @foreach(bulanSPP() as $bulan)
                @php
                    // Tentukan tahun untuk setiap bulan
                    $tahun_bulan = $bulan >= 7 ? $tahun_awal : $tahun_awal + 1;
                    $namaBulan = Carbon\Carbon::create(null, $bulan, 1)->locale('id')->translatedFormat('F Y');
                    $tagihan = isset($tagihan_per_bulan[$bulan]) ? $tagihan_per_bulan[$bulan] : null;
                    
                    // Siapkan keterangan jika ada tagihan
                    $keterangan = '';
                    if ($tagihan) {
                        $items = collect($tagihan['items']);
                        $keterangan = $items->pluck('nama_biaya')->join(', ');
                    }
                @endphp
                <tr>
                    <td style="height: 30px;">{{ $no++ }}</td>
                    <td>{{ $namaBulan }}</td>
                    <td class="amount">{{ $tagihan ? formatRupiah($tagihan['total_tagihan'], 'Rp. ') : '-' }}</td>
                    <td>{{ $tagihan ? $tagihan['tanggal_bayar'] : '' }}</td>
                    <td></td>
                    <td>{{ $keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div>{{ getInstansiSetting('nama_instansi') }}, {{ now()->translatedFormat('d F Y') }}</div>
        <div style="margin-top: 10px;">{{ getInstansiSetting('nama_jabatan') ?: 'Bendahara' }}</div>
        @if(getInstansiTtdUrl())
            <img src="{{ getInstansiTtdUrl() }}" alt="Tanda Tangan" style="max-height: 40px; margin: 10px 0;">
        @else
            <div class="signature-line"></div>
        @endif
        <div>{{ strtoupper(getInstansiSetting('nama_penanggung_jawab') ?: auth()->user()->name) }}</div>
    </div>
</body>
</html> 
<?php
function formatRupiah($nominal, $prefix = null) {
    $prefix = $prefix ? $prefix : 'Rp. ';
    return $prefix . number_format($nominal, 0, ',', '.');
}

function formatTanggalIndonesia($tanggal, $format = 'd F Y') {
    if (!$tanggal) {
        return '-';
    }
    return \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat($format);
}

function formatTanggalWaktuIndonesia($tanggal, $format = 'd F Y, H:i') {
    if (!$tanggal) {
        return '-';
    }
    return \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat($format);
}
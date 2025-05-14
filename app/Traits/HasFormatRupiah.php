<?php

namespace App\Traits;

trait HasFormatRupiah
{
    public function formatRupiah($attribute, $prefix = null) {
        $nominal = $this->{$attribute};
        $prefix = $prefix ? $prefix : 'Rp ';
        return $prefix . number_format($nominal, 0, ',', '.');
    }
}
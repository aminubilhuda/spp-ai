<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaImportTemplate implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Contoh data untuk template
        return [
            [
                'nama_wajib' => 'Ahmad Fadillah',
                'nisn_wajib' => '1234567890',
                'nis_wajib' => '2024001',
                'jenis_kelamin_wajib_lp_atau_laki_lakiperempuan' => 'Laki-laki',
                'kelas_wajib_contoh_x_xi_xii' => 'XII',
                'angkatan_wajib_contoh_2022' => '2022',
                'jurusan_wajib' => 'RPL',
                'wali_murid_opsional' => 'Budi Santoso',
                'status_wali_opsional_ayah_ibu_atau_wali' => 'Ayah',
                'email_wali_opsional' => 'budi.santoso@email.com',
                'nohp_wali_opsional' => '081234567890'
            ],
            [
                'nama_wajib' => 'Siti Nurhaliza',
                'nisn_wajib' => '1234567891',
                'nis_wajib' => '2024002',
                'jenis_kelamin_wajib_lp_atau_laki_lakiperempuan' => 'Perempuan',
                'kelas_wajib_contoh_x_xi_xii' => 'XI',
                'angkatan_wajib_contoh_2022' => '2023',
                'jurusan_wajib' => 'AKL',
                'wali_murid_opsional' => 'Siti Aminah',
                'status_wali_opsional_ayah_ibu_atau_wali' => 'Ibu',
                'email_wali_opsional' => 'siti.aminah@email.com',
                'nohp_wali_opsional' => '089876543210'
            ],
            [
                'nama_wajib' => 'Muhammad Rizki',
                'nisn_wajib' => '1234567892',
                'nis_wajib' => '2024003',
                'jenis_kelamin_wajib_lp_atau_laki_lakiperempuan' => 'Laki-laki',
                'kelas_wajib_contoh_x_xi_xii' => 'X',
                'angkatan_wajib_contoh_2022' => '2024',
                'jurusan_wajib' => 'BD',
                'wali_murid_opsional' => 'Rizki Pratama',
                'status_wali_opsional_ayah_ibu_atau_wali' => 'Wali',
                'email_wali_opsional' => 'rizki.pratama@email.com',
                'nohp_wali_opsional' => '087654321098'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nama_wajib',
            'nisn_wajib',
            'nis_wajib',
            'jenis_kelamin_wajib_lp_atau_laki_lakiperempuan',
            'kelas_wajib_contoh_x_xi_xii',
            'angkatan_wajib_contoh_2022',
            'jurusan_wajib',
            'wali_murid_opsional',
            'status_wali_opsional_ayah_ibu_atau_wali',
            'email_wali_opsional',
            'nohp_wali_opsional',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
            // Style contoh data
            'A2:K4' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6'],
                ],
            ],
        ];
    }
}
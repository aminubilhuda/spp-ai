<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class SiswaImportTemplate implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            // Provide sample data as guidance
            [
                'John Doe',               // nama
                '12345678',               // nisn
                '1234567',                // nis
                'Laki-laki',              // jenis_kelamin
                '11',                     // kelas
                '2022',                   // angkatan
                'IPA',                    // jurusan
                'Ahmad (Ayah)',           // wali_murid
                'Ayah',                   // wali_status 
                'Jl. Contoh Alamat No. 123', // alamat
                '08123456789'             // nomor_telepon
            ],
            // Empty row for user to fill
            [
                '', '', '', '', '', '', '', '', '', '', ''
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nama (Wajib)',
            'NISN (Wajib)',
            'NIS (Wajib)',
            'Jenis Kelamin (Wajib, L/P atau Laki-laki/Perempuan)',
            'Kelas (Wajib, contoh: X, XI, XII)',
            'Angkatan (Wajib, contoh: 2022)',
            'Jurusan (Wajib)',
            'Wali Murid (Opsional)',
            'Status Wali (Opsional: Ayah, Ibu, atau Wali)',
            'Alamat (Opsional)',
            'Nomor Telepon (Opsional)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add notes in cell A3
        $sheet->setCellValue('A4', 'Catatan:');
        $sheet->setCellValue('A5', '1. Semua kolom yang ditandai (Wajib) harus diisi');
        $sheet->setCellValue('A6', '2. Jurusan harus sesuai dengan yang ada di sistem (IPA, IPS, dll)');
        $sheet->setCellValue('A7', '3. Format file yang diunggah harus Excel (.xlsx atau .xls)');
        $sheet->setCellValue('A8', '4. Jenis Kelamin: L atau P / Laki-laki atau Perempuan');
        $sheet->setCellValue('A9', '5. Status Wali: Diisi dengan "Ayah", "Ibu", atau "Wali"');
        
        return [
            1 => ['font' => ['bold' => true]],
            'A4:A9' => ['font' => ['italic' => true, 'bold' => true]],
            2 => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCFFF1']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // nama
            'B' => 20,  // nisn
            'C' => 20,  // nis
            'D' => 25,  // jenis_kelamin
            'E' => 15,  // kelas
            'F' => 15,  // angkatan
            'G' => 20,  // jurusan
            'H' => 20,  // wali_murid
            'I' => 20,  // wali_status
            'J' => 35,  // alamat
            'K' => 20,  // nomor_telepon
        ];
    }
}
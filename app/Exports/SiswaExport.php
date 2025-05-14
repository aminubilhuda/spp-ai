<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    protected $jurusan;
    protected $kelas;
    protected $angkatan;

    public function __construct($search = null, $jurusan = null, $kelas = null, $angkatan = null)
    {
        $this->search = $search;
        $this->jurusan = $jurusan;
        $this->kelas = $kelas;
        $this->angkatan = $angkatan;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Siswa::query();

        // Apply filters if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nisn', 'like', '%' . $this->search . '%')
                  ->orWhere('nis', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->jurusan) {
            $query->where('jurusan_id', $this->jurusan);
        }

        if ($this->kelas) {
            $query->where('kelas', $this->kelas);
        }

        if ($this->angkatan) {
            $query->where('angkatan', $this->angkatan);
        }

        return $query->get();
    }

    /**
     * @var Siswa $siswa
     */
    public function map($siswa): array
    {
        return [
            $siswa->nama,
            $siswa->nisn,
            $siswa->nis,
            $siswa->kelas,
            $siswa->angkatan,
            $siswa->jurusan_nama,
            $siswa->alamat,
            $siswa->nomor_telepon,
            $siswa->created_at->format('d/m/Y'),
            $siswa->updated_at->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NISN',
            'NIS',
            'Kelas',
            'Angkatan',
            'Jurusan',
            'Alamat',
            'Nomor Telepon',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text with background color
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6F1FF'],
                ],
            ],
        ];
    }
}
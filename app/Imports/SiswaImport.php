<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SiswaImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $errors = [];
    protected $successCount = 0;
    protected $processed = [];

    public function collection(Collection $rows)
    {
        // Debug raw data
        Log::info("Raw Excel data: " . json_encode($rows));
        
        // Skip empty Excel or just with headers
        if ($rows->isEmpty()) {
            $this->errors[] = "File Excel kosong atau tidak memiliki data yang valid.";
            return;
        }
        
        // Debug headers
        Log::info("Raw headers: " . json_encode($this->getHeadersFromRows($rows)));
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Process each row that is not empty
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    Log::info("Skipping empty row at index: " . $index);
                    continue;
                }
                
                // Debug each row
                Log::info("Processing row #" . ($index+1) . ": " . json_encode($row));

                // Extract data from row with various possible header formats
                $data = $this->extractStudentData($row);
                
                // Debug extracted data
                Log::info("Extracted data: " . json_encode($data));
                
                // Check if required data is missing
                if ($this->isMissingRequiredData($data)) {
                    $this->errors[] = "Baris #" . ($index+1) . " dilewati karena data wajib tidak lengkap.";
                    continue;
                }
                
                // Check if student already exists
                if ($this->isStudentExists($data['nisn'])) {
                    $this->errors[] = "Siswa dengan NISN: " . $data['nisn'] . " sudah ada dalam sistem.";
                    continue;
                }
                
                // Find jurusan
                $jurusan = $this->findJurusan($data['jurusan']);
                
                // Handle wali if provided
                $waliData = $this->handleWali($data);
                
                try {
                    // Final data for creating student
                    $siswaData = [
                        'nama' => $data['nama'],
                        'nisn' => $data['nisn'],
                        'nis' => $data['nis'],
                        'jenis_kelamin' => $this->normalizeJenisKelamin($data['jenis_kelamin']),
                        'kelas' => $data['kelas'],
                        'angkatan' => $data['angkatan'],
                        'jurusan_id' => $jurusan ? $jurusan->id : null,
                        'wali_id' => $waliData['wali_id'],
                        'wali_status' => $waliData['wali_status'],
                        'user_id' => auth()->id(),
                    ];
                    
                    Log::info("Creating siswa with data: " . json_encode($siswaData));
                    
                    // Create student record
                    $siswa = Siswa::create($siswaData);
                    
                    // Track for debugging
                    $this->processed[] = [
                        'id' => $siswa->id,
                        'nama' => $siswa->nama,
                        'nisn' => $siswa->nisn
                    ];
                    
                    $this->successCount++;
                    Log::info("Success create student #" . $this->successCount . ": " . $siswa->nama);
                    
                } catch (\Exception $e) {
                    $this->errors[] = "Error menyimpan siswa " . $data['nama'] . ": " . $e->getMessage();
                    Log::error("Error importing student row #" . ($index+1) . ": " . $e->getMessage());
                    Log::error($e->getTraceAsString());
                }
            }
            
            // If successful, commit transaction
            if (count($this->errors) == 0) {
                DB::commit();
                Log::info("Import completed successfully. " . $this->successCount . " records imported.");
                Log::info("Processed records: " . json_encode($this->processed));
            } else {
                // If errors occurred, rollback all changes
                if ($this->successCount > 0) {
                    DB::commit(); // Save the successful ones if there are any
                    Log::info("Partial import completed. " . $this->successCount . " records imported with some errors.");
                } else {
                    DB::rollBack();
                    Log::warning("Import failed with errors. No records imported.");
                }
                Log::warning("Errors: " . json_encode($this->errors));
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = "Fatal error during import: " . $e->getMessage();
            Log::error("Fatal error during import: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
    
    /**
     * Extract headers from the Excel rows
     */
    private function getHeadersFromRows(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return [];
        }
        
        $firstRow = $rows->first();
        return array_keys($firstRow->toArray());
    }
    
    /**
     * Check if row is empty (all values null or empty string)
     */
    private function isEmptyRow($row)
    {
        if (!$row || empty($row)) {
            return true;
        }
        
        $values = array_filter($row->toArray(), function($value) {
            return $value !== null && $value !== '';
        });
        
        return count($values) === 0;
    }
    
    /**
     * Extract student data from row with various possible header names
     */
    private function extractStudentData($row)
    {
        $data = [];
        
        // Try different possible header names for each field
        $data['nama'] = $row['nama_wajib'] ?? $row['nama'] ?? $row['name'] ?? null;
        $data['nisn'] = $row['nisn_wajib'] ?? $row['nisn'] ?? null;
        $data['nis'] = $row['nis_wajib'] ?? $row['nis'] ?? null;
        $data['kelas'] = $row['kelas_wajib_contoh_x_xi_xii'] ?? $row['kelas'] ?? 
                        $row['kelas_wajib_contoh_10_11_12'] ?? $row['class'] ?? null;
        $data['angkatan'] = $row['angkatan_wajib_contoh_2022'] ?? $row['angkatan'] ?? 
                           $row['angkatan_wajib'] ?? $row['tahun'] ?? null;
        $data['jurusan'] = $row['jurusan_wajib'] ?? $row['jurusan'] ?? 
                          $row['major'] ?? $row['department'] ?? null;
        $data['jenis_kelamin'] = $row['jenis_kelamin_wajib_lp_atau_laki_lakiperempuan'] ?? 
                                $row['jenis_kelamin'] ?? $row['gender'] ?? $row['sex'] ?? null;
        
        // Optional fields
        $data['wali_murid'] = $row['wali_murid_opsional'] ?? $row['wali_murid'] ?? 
                             $row['wali'] ?? $row['guardian'] ?? $row['parent'] ?? null;
        $data['wali_status'] = $row['status_wali_opsional_ayah_ibu_atau_wali'] ?? 
                              $row['wali_status'] ?? $row['status_wali'] ?? null;

        // Log the raw data before extraction
        Log::info("Extracting data from row:", [
            'raw_row' => $row,
            'extracted' => $data
        ]);
        
        return $data;
    }
    
    /**
     * Check if required data is missing
     */
    private function isMissingRequiredData($data)
    {
        return empty($data['nama']) || 
               empty($data['nisn']) || 
               empty($data['nis']) || 
               empty($data['kelas']) || 
               empty($data['angkatan']) || 
               empty($data['jurusan']) || 
               empty($data['jenis_kelamin']);
    }
    
    /**
     * Check if student exists
     */
    private function isStudentExists($nisn)
    {
        return Siswa::where('nisn', $nisn)->exists();
    }
    
    /**
     * Find jurusan by name with fuzzy matching
     */
    private function findJurusan($jurusanName)
    {
        if (empty($jurusanName)) {
            return null;
        }

        // Common jurusan mappings
        $mappings = [
            'IPA' => ['RPL', 'REKAYASA PERANGKAT LUNAK'],
            'IPS' => ['AKL', 'AKUNTANSI'],
            'TKJ' => ['BD', 'BISNIS DIGITAL']
        ];

        // Check direct match first
        $jurusan = Jurusan::where('nama', 'LIKE', '%' . $jurusanName . '%')->first();
        
        if (!$jurusan) {
            // Try mapped values
            foreach ($mappings as $oldName => $newNames) {
                if (strtoupper($jurusanName) === $oldName) {
                    foreach ($newNames as $newName) {
                        $jurusan = Jurusan::where('nama', 'LIKE', '%' . $newName . '%')->first();
                        if ($jurusan) break;
                    }
                }
            }
        }

        if ($jurusan) {
            Log::info("Jurusan found: " . $jurusan->nama . " (ID: " . $jurusan->id . ")");
        } else {
            Log::warning("Jurusan not found for: " . $jurusanName);
        }
        
        return $jurusan;
    }
    
    /**
     * Handle wali data
     */
    private function handleWali($data)
    {
        $wali_id = null;
        $wali_status = null;
        
        if (!empty($data['wali_murid'])) {
            // Debug - processing wali
            Log::info("Processing wali_murid: " . $data['wali_murid']);
            
            // Check if guardian already exists
            $wali = User::where('name', $data['wali_murid'])->where('akses', 'wali')->first();
            
            if ($wali) {
                Log::info("Wali found: " . $wali->name . " (ID: " . $wali->id . ")");
            } else {
                // Create new guardian
                try {
                    $email = Str::slug($data['wali_murid']) . '@mail.com';
                    $nohp = '08' . rand(100000000, 999999999); // Generate random phone number
                    Log::info("Creating new wali with email: " . $email . " and nohp: " . $nohp);
                    
                    $wali = User::create([
                        'name' => $data['wali_murid'],
                        'email' => $email,
                        'password' => bcrypt('password'),
                        'akses' => 'wali',
                        'nohp' => $nohp
                    ]);
                    
                    Log::info("New wali created with ID: " . $wali->id);
                } catch (\Exception $e) {
                    Log::error("Error creating wali: " . $e->getMessage());
                    // In case of error, we'll continue without a wali
                }
            }
            
            if (isset($wali)) {
                $wali_id = $wali->id;
                $wali_status = $data['wali_status'] ?? 'Wali';
            }
        }
        
        return [
            'wali_id' => $wali_id,
            'wali_status' => $wali_status,
        ];
    }
    
    /**
     * Normalize jenis_kelamin value
     */
    private function normalizeJenisKelamin($jenisKelamin)
    {
        if (empty($jenisKelamin)) {
            return null;
        }
        
        $normalized = strtolower($jenisKelamin);
        
        if (in_array($normalized, ['l', 'laki', 'laki-laki', 'lakilaki', 'male', 'm'])) {
            return 'Laki-laki';
        }
        
        if (in_array($normalized, ['p', 'perempuan', 'wanita', 'female', 'f', 'w'])) {
            return 'Perempuan';
        }
        
        // Default return the original value if not recognized
        return ucfirst($jenisKelamin);
    }

    public function rules(): array
    {
        // We do validation manually in the collection method
        return [];
    }

    /**
     * Get the imported validation errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Get the number of successfully imported records
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }
    
    /**
     * Get detailed list of processed records
     */
    public function getProcessedRecords()
    {
        return $this->processed;
    }
}
@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Import Data Siswa</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {!! session('error') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Informasi Fitur -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Informasi Import Siswa</h6>
                        <ul class="mb-0">
                            <li><strong>Auto-Create Wali:</strong> Sistem akan otomatis membuat user wali jika belum ada</li>
                            <li><strong>Data Wali:</strong> Jika kolom wali diisi, sistem akan membuat/mengupdate data wali</li>
                            <li><strong>Email & No. HP:</strong> Bisa diisi manual atau sistem generate otomatis</li>
                            <li><strong>Password Default:</strong> Wali baru akan memiliki password: <code>password</code></li>
                            <li><strong>Format Excel:</strong> Gunakan template yang disediakan untuk hasil terbaik</li>
                        </ul>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-4">
                        <h6>Langkah 1: Download Template</h6>
                        <p class="text-muted">Download template Excel yang sudah disiapkan dengan format yang benar.</p>
                        <a href="{{ route('siswa.import.template') }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Download Template Excel
                        </a>
                    </div>

                    <!-- Upload File -->
                    <div class="mb-4">
                        <h6>Langkah 2: Upload File Excel</h6>
                        <p class="text-muted">Upload file Excel yang sudah diisi dengan data siswa.</p>
                        
                        <form action="{{ route('siswa.import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Pilih File Excel</label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                       id="file" name="file" accept=".xlsx,.xls" required>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Format yang didukung: .xlsx, .xls (Maksimal 2MB)</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Data
                            </button>
                            
                            <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </form>
                    </div>

                    <!-- Panduan Format -->
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-book"></i> Panduan Format Data</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Data Siswa (Wajib):</h6>
                                    <ul class="small">
                                        <li><strong>Nama:</strong> Nama lengkap siswa</li>
                                        <li><strong>NISN:</strong> Nomor Induk Siswa Nasional (unik)</li>
                                        <li><strong>NIS:</strong> Nomor Induk Sekolah (unik)</li>
                                        <li><strong>Jenis Kelamin:</strong> L/P atau Laki-laki/Perempuan</li>
                                        <li><strong>Kelas:</strong> X, XI, atau XII</li>
                                        <li><strong>Angkatan:</strong> Tahun masuk (contoh: 2022)</li>
                                        <li><strong>Jurusan:</strong> RPL, AKL, atau BD</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Data Wali (Opsional):</h6>
                                    <ul class="small">
                                        <li><strong>Wali Murid:</strong> Nama wali murid</li>
                                        <li><strong>Status Wali:</strong> Ayah, Ibu, atau Wali</li>
                                        <li><strong>Email Wali:</strong> Email wali (opsional)</li>
                                        <li><strong>No. HP Wali:</strong> Nomor telepon wali (opsional)</li>
                                    </ul>
                                    <div class="alert alert-warning small">
                                        <strong>Catatan:</strong> Jika email/nohp tidak diisi, sistem akan generate otomatis
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Preview file name
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            const fileInfo = document.createElement('div');
            fileInfo.className = 'form-text text-success mt-2';
            fileInfo.innerHTML = '<i class="fas fa-check"></i> File dipilih: ' + fileName;
            
            // Remove previous file info
            const prevInfo = this.parentNode.querySelector('.text-success');
            if (prevInfo) prevInfo.remove();
            
            this.parentNode.appendChild(fileInfo);
        }
    });
</script>
@endpush

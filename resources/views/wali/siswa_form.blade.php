@extends('layouts.app_sneat_wali')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">{{ $title }}</h5>
            <div class="card-body">
                <form action="{{ $action }}" method="POST">
                    @csrf
                    @method($method)
                    
                    {{-- Data Pribadi --}}
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nama">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                                    value="{{ old('nama', $siswa->nama) }}" placeholder="Masukkan nama lengkap" autofocus>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Data Akademik --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nisn">NISN</label>
                                <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" 
                                    value="{{ old('nisn', $siswa->nisn) }}" placeholder="Masukkan NISN">
                                @error('nisn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nis">NIS</label>
                                <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" 
                                    value="{{ old('nis', $siswa->nis) }}" placeholder="Masukkan NIS">
                                @error('nis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jurusan_id">Jurusan</label>
                                <select name="jurusan_id" class="form-control select2 @error('jurusan_id') is-invalid @enderror">
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($jurusan as $item)
                                        <option value="{{ $item->id }}" {{ old('jurusan_id', $siswa->jurusan_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jurusan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kelas">Kelas</label>
                                <select name="kelas" class="form-control select2 @error('kelas') is-invalid @enderror">
                                    <option value="">Pilih Kelas</option>
                                    <option value="X" {{ old('kelas', $siswa->kelas) == 'X' ? 'selected' : '' }}>X (Sepuluh)</option>
                                    <option value="XI" {{ old('kelas', $siswa->kelas) == 'XI' ? 'selected' : '' }}>XI (Sebelas)</option>
                                    <option value="XII" {{ old('kelas', $siswa->kelas) == 'XII' ? 'selected' : '' }}>XII (Dua Belas)</option>
                                </select>
                                @error('kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="angkatan">Angkatan</label>
                                <input type="text" name="angkatan" class="form-control @error('angkatan') is-invalid @enderror" 
                                    value="{{ old('angkatan', $siswa->angkatan) }}" placeholder="Contoh: 2023">
                                @error('angkatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">SIMPAN</button>
                            <a href="{{ route('wali.siswa.index') }}" class="btn btn-danger">BATAL</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 untuk dropdown
        $('.select2').select2({
            width: '100%' // Memastikan select2 mengambil lebar penuh
        });
    });
</script>
@endsection 
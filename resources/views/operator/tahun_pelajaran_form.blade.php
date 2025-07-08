@extends('layouts.app_sneat', ['title' => isset($tahunPelajaran) ? 'Edit Tahun Pelajaran' : 'Tambah Tahun Pelajaran'])

@section('content')
    <div class="row justify-content-center">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="col-md-8">
            <div class="card">
                <h5 class="card-header">{{ isset($tahunPelajaran) ? 'Edit' : 'Tambah' }} Tahun Pelajaran</h5>
                <div class="card-body">
                    <form action="{{ isset($tahunPelajaran) ? route('tahun-pelajaran.update', $tahunPelajaran->id) : route('tahun-pelajaran.store') }}" method="POST">
                        @csrf
                        @if(isset($tahunPelajaran))
                            @method('PUT')
                        @endif
                        <div class="mb-3 form-group">
                            <label for="nama" class="form-label">Nama Tahun Pelajaran</label>
                            <input type="text" autofocus name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $tahunPelajaran->nama ?? '') }}" required placeholder="Contoh: 2024/2025">
                            @error('nama')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-save me-1"></i>
                                Simpan
                            </button>
                            <a href="{{ route('tahun-pelajaran.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 
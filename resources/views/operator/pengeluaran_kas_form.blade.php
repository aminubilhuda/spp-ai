@extends('layouts.app_sneat')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <h5 class="card-header">{{ $title ?? 'Tambah Pengeluaran Kas' }}</h5>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('pengeluaran-kas.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ old('jumlah') }}" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select name="kategori" id="kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Setoran Bank" {{ old('kategori')=='Setoran Bank' ? 'selected' : '' }}>Setoran Bank</option>
                            <option value="Operasional" {{ old('kategori')=='Operasional' ? 'selected' : '' }}>Operasional</option>
                            <option value="ATK" {{ old('kategori')=='ATK' ? 'selected' : '' }}>ATK</option>
                            <option value="Listrik" {{ old('kategori')=='Listrik' ? 'selected' : '' }}>Listrik</option>
                            <option value="Lainnya" {{ old('kategori')=='Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" id="keterangan" class="form-control" value="{{ old('keterangan') }}">
                    </div>
                    <div class="card-footer bg-white border-0 px-0">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="{{ route('pengeluaran-kas.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 
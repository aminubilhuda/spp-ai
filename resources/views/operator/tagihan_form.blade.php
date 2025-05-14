@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <form action="{{ isset($model->id) ? route($route, $model->id) : route($route) }}" method="POST">
                        @csrf
                        @if (isset($model->id))
                            @method('PUT')
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Pilih Biaya</label>
                                <div class="row">
                                    @foreach ($biaya as $item)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="biaya_id[]" value="{{ $item->id }}"
                                                    class="form-check-input @error('biaya_id') is-invalid @enderror"
                                                    id="biaya_{{ $item->id }}" data-jumlah="{{ $item->jumlah }}"
                                                    {{ is_array(old('biaya_id')) && in_array($item->id, old('biaya_id')) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="biaya_{{ $item->id }}">
                                                    {{ $item->nama }} ({{ formatRupiah($item->jumlah) }})
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('biaya_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Angkatan</label>
                                <select name="angkatan" class="form-control @error('angkatan') is-invalid @enderror">
                                    <option value="">Pilih Angkatan</option>
                                    @foreach ($angkatan as $item)
                                        <option value="{{ $item }}"
                                            {{ old('angkatan') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('angkatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jurusan <small class="text-muted">(opsional)</small></label>
                                <select name="jurusan" class="form-control @error('jurusan') is-invalid @enderror">
                                    <option value="">Semua Jurusan</option>
                                    @foreach ($jurusan as $kode => $nama)
                                        <option value="{{ $kode }}"
                                            {{ old('jurusan') == $kode ? 'selected' : '' }}>
                                            {{ $nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jurusan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kelas <small class="text-muted">(opsional)</small></label>
                                <select name="kelas" class="form-control @error('kelas') is-invalid @enderror">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelas as $item)
                                        <option value="{{ $item }}" {{ old('kelas') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Tagihan</label>
                                <input type="date" name="tanggal_tagihan"
                                    class="form-control @error('tanggal_tagihan') is-invalid @enderror"
                                    value="{{ old('tanggal_tagihan') }}">
                                @error('tanggal_tagihan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tanggal_jatuh_tempo"
                                    class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror"
                                    value="{{ old('tanggal_jatuh_tempo') }}">
                                @error('tanggal_jatuh_tempo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ $button }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

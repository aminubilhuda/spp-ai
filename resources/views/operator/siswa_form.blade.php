@extends('layouts.app_sneat')

@section('styles')
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="row justify-content-center">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">

                    <form action="{{ isset($id) ? route($action, $id) : (isset($action) ? route($action) : '#') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @if ($method === 'PUT')
                            @method('PUT')
                        @endif

                        <!-- Data Siswa -->
                        <div class="mb-3 form-group">
                            <label for="nama" class="form-label">Nama Siswa</label>
                            <input type="text" class="form-control" id="nama" name="nama" autofocus
                                value="{{ old('nama') ?? $model->nama }}" {{ isset($isShow) ? 'readonly' : '' }}>
                            <span class="text-danger">{{ $errors->first('nama') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="nisn" class="form-label">NISN</label>
                            <input type="text" class="form-control" id="nisn" name="nisn"
                                value="{{ old('nisn') ?? $model->nisn }}" {{ isset($isShow) ? 'readonly' : '' }}>
                            <span class="text-danger">{{ $errors->first('nisn') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="nis" class="form-label">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis"
                                value="{{ old('nis') ?? $model->nis }}" {{ isset($isShow) ? 'readonly' : '' }}>
                            <span class="text-danger">{{ $errors->first('nis') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="foto" class="form-label">Foto Siswa</label>
                            @if ($model->foto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $model->foto) }}" alt="Foto Siswa"
                                        class="img-thumbnail" style="max-height: 150px">
                                </div>
                            @endif
                            <input type="file" class="form-control" id="foto" name="foto"
                                {{ isset($isShow) ? 'disabled' : '' }}>
                            <small class="text-muted">Upload foto dengan format JPG, PNG, atau JPEG. Maksimal 2MB.</small>
                            <span class="text-danger">{{ $errors->first('foto') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" id="jenis_kelamin"
                                {{ isset($isShow) ? 'disabled' : '' }}>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki"
                                    {{ old('jenis_kelamin') == 'Laki-laki' || $model->jenis_kelamin == 'Laki-laki' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="Perempuan"
                                    {{ old('jenis_kelamin') == 'Perempuan' || $model->jenis_kelamin == 'Perempuan' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>
                            <span class="text-danger">{{ $errors->first('jenis_kelamin') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="jurusan_id" class="form-label">Jurusan</label>
                            <select name="jurusan_id" class="form-control" id="jurusan_id"
                                {{ isset($isShow) ? 'disabled' : '' }}>
                                <option value="">Pilih Jurusan</option>
                                @foreach (\App\Models\Jurusan::all() as $jurusan)
                                    <option value="{{ $jurusan->id }}"
                                        {{ old('jurusan_id') == $jurusan->id || $model->jurusan_id == $jurusan->id ? 'selected' : '' }}>
                                        {{ $jurusan->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger">{{ $errors->first('jurusan_id') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select name="kelas" class="form-control" id="kelas"
                                {{ isset($isShow) ? 'disabled' : '' }}>
                                <option value="">Pilih Kelas</option>
                                <option value="X" {{ old('kelas') == 'X' || $model->kelas == 'X' ? 'selected' : '' }}>
                                    X</option>
                                <option value="XI"
                                    {{ old('kelas') == 'XI' || $model->kelas == 'XI' ? 'selected' : '' }}>XI</option>
                                <option value="XII"
                                    {{ old('kelas') == 'XII' || $model->kelas == 'XII' ? 'selected' : '' }}>XII</option>
                            </select>
                            <span class="text-danger">{{ $errors->first('kelas') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="angkatan" class="form-label">Angkatan</label>
                            <input type="text" class="form-control" id="angkatan" name="angkatan"
                                value="{{ old('angkatan') ?? ($model->angkatan ?? date('Y')) }}"
                                {{ isset($isShow) ? 'readonly' : '' }}>
                            <span class="text-danger">{{ $errors->first('angkatan') }}</span>
                        </div>

                        <!-- Data Wali Murid -->
                        <div class="mb-3 form-group">
                            <label for="wali_id" class="form-label">Wali Murid</label>
                            <select name="wali_id" class="form-control select2" id="wali_id"
                                {{ isset($isShow) ? 'disabled' : '' }}>
                                <option value="">Pilih Wali Murid</option>
                                @foreach (\App\Models\User::where('akses', 'wali')->get() as $wali)
                                    <option value="{{ $wali->id }}"
                                        {{ old('wali_id') == $wali->id || $model->wali_id == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger">{{ $errors->first('wali_id') }}</span>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="wali_status" class="form-label">Status Wali</label>
                            <select name="wali_status" class="form-control" id="wali_status"
                                {{ isset($isShow) ? 'disabled' : '' }}>
                                <option value="">Pilih Status</option>
                                <option value="Ayah"
                                    {{ old('wali_status') == 'Ayah' || $model->wali_status == 'Ayah' ? 'selected' : '' }}>
                                    Ayah</option>
                                <option value="Ibu"
                                    {{ old('wali_status') == 'Ibu' || $model->wali_status == 'Ibu' ? 'selected' : '' }}>
                                    Ibu</option>
                                <option value="Wali"
                                    {{ old('wali_status') == 'Wali' || $model->wali_status == 'Wali' ? 'selected' : '' }}>
                                    Wali</option>
                            </select>
                            <span class="text-danger">{{ $errors->first('wali_status') }}</span>
                        </div>

                        @if (isset($isShow))
                            <a href="{{ route('siswa.index') }}" class="btn btn-primary">{{ $button }}</a>
                        @else
                            <button type="submit" class="btn btn-primary">{{ $button }}</button>
                        @endif
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#wali_id').select2({
                placeholder: "Pilih Wali Murid",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection

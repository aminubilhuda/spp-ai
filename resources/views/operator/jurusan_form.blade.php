@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <form action="{{ is_array($route) ? route($route[0], $route[1]) : route($route) }}" method="POST">
                        @csrf
                        @if ($method == 'PUT')
                            @method('PUT')
                        @endif

                        <div class="form-group mb-3">
                            <label for="nama">Nama Jurusan</label>
                            <input type="text" name="nama" id="nama" class="form-control"
                                value="{{ old('nama', $jurusan->nama) }}" autofocus>
                            <span class="text-danger">{{ $errors->first('nama') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ old('keterangan', $jurusan->keterangan) }}</textarea>
                            <span class="text-danger">{{ $errors->first('keterangan') }}</span>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ $button }}</button>
                            <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

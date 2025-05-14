@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <form action="{{ route($action, $model->exists ? $model->id : '') }}" method="POST">
                        @csrf
                        @if ($method == 'PUT')
                            @method('PUT')
                        @endif

                        <div class="form-group mb-3">
                            <label for="nama">Nama Biaya</label>
                            <input type="text" name="nama" id="nama" class="form-control"
                                value="{{ old('nama', $model->nama) }}" autofocus>
                            <span class="text-danger">{{ $errors->first('nama') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="jumlah">Jumlah (Rp)</label>
                            <input type="text" name="jumlah" id="jumlah" class="form-control rupiah"
                                value="{{ old('jumlah', $model->jumlah) }}">
                            <span class="text-danger">{{ $errors->first('jumlah') }}</span>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ $button }}</button>
                            <a href="{{ route('biaya.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

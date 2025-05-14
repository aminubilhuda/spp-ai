@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">

                    <form action="{{ isset($id) ? route($action, $id) : route($action) }}" method="post">
                        @csrf
                        @if ($method === 'PUT')
                            @method('PUT')
                        @endif
                        <div class="mb-3 form-group">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" autofocus
                                value="{{ old('name') ?? $model->name }}">
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email') ?? $model->email }}">
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="nohp" class="form-label">Nomor HP</label>
                            <input type="number" class="form-control" id="nohp" name="nohp"
                                value="{{ old('nohp') ?? $model->nohp }}">
                            <span class="text-danger">{{ $errors->first('nohp') }}</span>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            @if ($method === 'PUT')
                                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah
                                    password</small>
                            @endif
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="akses" class="form-label">Akses</label>
                            <select name="akses" class="form-control" id="akses">
                                <option value="">Pilih Akses</option>
                                <option value="operator"
                                    {{ old('akses') == 'operator' || $model->akses == 'operator' ? 'selected' : '' }}>
                                    Operator</option>
                                <option value="admin"
                                    {{ old('akses') == 'admin' || $model->akses == 'admin' ? 'selected' : '' }}>
                                    Admin</option>
                            </select>
                            <span class="text-danger">{{ $errors->first('akses') }}</span>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ $button }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

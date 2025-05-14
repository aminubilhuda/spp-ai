@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <td width="20%">Nama</td>
                                <td>: {{ $model->name }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>: {{ $model->email }}</td>
                            </tr>
                            <tr>
                                <td>No HP</td>
                                <td>: {{ $model->nohp }}</td>
                            </tr>
                            <tr>
                                <td>Akses</td>
                                <td>: {{ $model->akses }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Buat</td>
                                <td>: {{ $model->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Ubah</td>
                                <td>: {{ $model->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route($routePrefix . '.edit', $model->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

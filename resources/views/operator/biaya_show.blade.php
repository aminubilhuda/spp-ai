@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Detail Biaya</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Nama Biaya</th>
                                <td>{{ $model->nama }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah</th>
                                <td>Rp {{ number_format($model->jumlah, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <td>{{ $model->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diupdate</th>
                                <td>{{ $model->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('biaya.index') }}" class="btn btn-secondary">Kembali</a>
                        <a href="{{ route('biaya.edit', $model->id) }}" class="btn btn-warning">Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

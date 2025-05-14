@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Detail Jurusan</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Nama Jurusan</th>
                                <td>{{ $jurusan->nama }}</td>
                            </tr>
                            <tr>
                                <th>Keterangan</th>
                                <td>{{ $jurusan->keterangan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <td>{{ $jurusan->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diupdate</th>
                                <td>{{ $jurusan->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">Kembali</a>
                        <a href="{{ route('jurusan.edit', $jurusan->id) }}" class="btn btn-warning">Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

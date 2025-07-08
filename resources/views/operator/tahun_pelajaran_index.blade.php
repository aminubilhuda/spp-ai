@extends('layouts.app_sneat')

@section('title', 'Tahun Pelajaran')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    <span>Daftar Tahun Pelajaran</span>
                    <a href="{{ route('tahun-pelajaran.create') }}" class="btn btn-primary btn-sm">Tambah Tahun Pelajaran</a>
                </h5>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Tahun Pelajaran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tahunPelajarans as $tp)
                                    <tr>
                                        <td>{{ $tp->nama }}</td>
                                        <td>
                                            @if($tp->is_aktif)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if(!$tp->is_aktif)
                                                    <a href="{{ route('tahun-pelajaran.set-aktif', $tp->id) }}" class="btn btn-sm btn-success">Set Aktif</a>
                                                @endif
                                                <a href="{{ route('tahun-pelajaran.edit', $tp->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('tahun-pelajaran.destroy', $tp->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 
@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Data Jurusan</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <a href="{{ route('jurusan.create') }}" class="btn btn-primary btn-sm float-end">
                                <i class="menu-icon tf-icons bx bx-plus"></i> Tambah Jurusan
                            </a>
                        </div>
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Jurusan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jurusans as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama }}</td>
                                        <td>{{ $item->keterangan }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('jurusan.show', $item->id) }}"
                                                    class="btn btn-info btn-sm me-2">
                                                    <i class="menu-icon tf-icons bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('jurusan.edit', $item->id) }}"
                                                    class="btn btn-warning btn-sm me-2">
                                                    <i class="menu-icon tf-icons bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('jurusan.destroy', $item->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="menu-icon tf-icons bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

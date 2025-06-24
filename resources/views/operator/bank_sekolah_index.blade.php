@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Data Bank Sekolah</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <a href="{{ route('bank-sekolah.create') }}" class="btn btn-primary btn-sm float-end">
                                <i class="menu-icon tf-icons bx bx-plus"></i> Tambah Bank Sekolah
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
                                    <th>Kode Bank</th>
                                    <th>Nama Bank</th>
                                    <th>No. Rekening</th>
                                    <th>Atas Nama</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($models as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->kode_bank }}</td>
                                        <td>{{ $item->nama_bank }}</td>
                                        <td>{{ $item->no_rekening }}</td>
                                        <td>{{ $item->atas_nama ?? '-' }}</td>
                                        <td>{{ $item->keterangan ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('bank-sekolah.show', $item->id) }}"
                                                    class="btn btn-info btn-sm me-2">
                                                    <i class="menu-icon tf-icons bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('bank-sekolah.edit', $item->id) }}"
                                                    class="btn btn-warning btn-sm me-2">
                                                    <i class="menu-icon tf-icons bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('bank-sekolah.destroy', $item->id) }}"
                                                    method="POST"
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
                                        <td colspan="7" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $models->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

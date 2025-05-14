@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary mb-3 btn-sm">Tambah
                                siswa</a>
                            <a href="{{ route($routePrefix . '.export') }}" class="btn btn-success mb-3 btn-sm">
                                <i class='bx bx-export'></i> Export Excel
                            </a>
                            <a href="{{ route($routePrefix . '.import.form') }}" class="btn btn-info mb-3 btn-sm">
                                <i class='bx bx-import'></i> Import Excel
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route($routePrefix . '.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari siswa (nama, NISN, NIS, jurusan, kelas)"
                                        value="{{ $search ?? '' }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    @if (isset($search) && $search != '')
                                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama Wali Murid</td>
                                    <td>Nama</td>
                                    <td>NISN</td>
                                    <td>NIS</td>
                                    <td>Foto</td>
                                    <td>Jurusan</td>
                                    <td>Kelas</td>
                                    <td>Angkatan</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse ($models as $siswa)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $siswa->wali->name }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        <td>{{ $siswa->nisn }}</td>
                                        <td>{{ $siswa->nis }}</td>
                                        <td>
                                            @if ($siswa->foto)
                                                <img src="{{ asset('storage/' . $siswa->foto) }}"
                                                    alt="Foto {{ $siswa->nama }}" class="img-thumbnail"
                                                    style="max-height: 50px">
                                            @else
                                                <span class="badge bg-label-warning">Belum ada foto</span>
                                            @endif
                                        </td>
                                        <td>{{ $siswa->jurusan->nama }}</td>
                                        <td>{{ $siswa->kelas }}</td>
                                        <td>{{ $siswa->angkatan }}</td>
                                        <td>
                                            <a href="{{ route($routePrefix . '.show', $siswa->id) }}"
                                                class="btn btn-sm btn-info"> <i class="fas fa-eye"></i> Detail</a>
                                            <a href="{{ route($routePrefix . '.edit', $siswa->id) }}"
                                                class="btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Edit</a>
                                            <form action="{{ route($routePrefix . '.destroy', $siswa->id) }}"
                                                method="post" class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Apakah anda yakin?')"><i class="fas fa-trash">
                                                    </i> Hapus</button>
                                            </form>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="card-footer">
                            @if (isset($search) && $search != '')
                                {{ $models->appends(['search' => $search])->links() }}
                            @else
                                {{ $models->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

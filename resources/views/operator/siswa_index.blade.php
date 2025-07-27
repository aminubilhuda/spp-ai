@extends('layouts.app_sneat')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/operator/siswa_index.css') }}">
@endsection

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
                <h5 class="card-header">
                    {{ $title }}
                    <div class="d-flex gap-2">
                        <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Tambah Siswa
                        </a>
                        <a href="{{ route($routePrefix . '.export') }}" class="btn btn-success btn-sm">
                            <i class='bx bx-export me-1'></i> Export Excel
                        </a>
                        <a href="{{ route($routePrefix . '.import.form') }}" class="btn btn-info btn-sm">
                            <i class='bx bx-import me-1'></i> Import Excel
                        </a>
                    </div>
                </h5>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 ms-auto search-filter-section">
                            <form action="{{ route($routePrefix . '.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari siswa (nama, NISN, NIS, jurusan, kelas)"
                                        value="{{ $search ?? '' }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search"></i>
                                    </button>
                                    @if (isset($search) && $search != '')
                                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-x"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Wali Murid</td>
                                    <td>Nama</td>
                                    <td>NISN</td>
                                    <!-- {{-- <td>NIS</td> --}} -->
                                    <!-- {{-- <td>Foto</td> --}} -->
                                    <!-- <td>Jurusan</td> -->
                                    <!-- <td>Kelas</td> -->
                                    <td>Angkatan</td>
                                    <td>Biaya SPP</td>
                                    <td>Status</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach ($models as $siswa)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $siswa->wali->name }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        <td>{{ $siswa->nisn }}</td>
                                        {{-- <td>{{ $siswa->nis }}</td> --}}
                                        {{-- <td>
                                            @if ($siswa->foto)
                                                <img src="{{ asset('storage/' . $siswa->foto) }}"
                                                    alt="Foto {{ $siswa->nama }}" class="img-thumbnail"
                                                    style="max-height: 50px">
                                            @else
                                                <span class="badge bg-label-warning">Belum ada foto</span>
                                            @endif
                                        </td> --}}
                                        <!-- <td>{{ $siswa->jurusan->nama }}</td> -->
                                        <!-- <td>{{ $siswa->kelas }}</td> -->
                                        <td>{{ $siswa->angkatan }}</td>
                                        <td>{{ formatRupiah($siswa->biaya_spp) }}</td>
                                        <td>{{ $siswa->status }}</td>
                                        <td class="text-nowrap">
                                            <div class="d-flex gap-1">
                                                <a href="{{ route($routePrefix . '.show', $siswa->id) }}"
                                                    class="btn btn-sm btn-info btn-icon"> <i class="bx bx-show"></i></a>
                                                <a href="{{ route($routePrefix . '.edit', $siswa->id) }}"
                                                    class="btn btn-sm btn-warning btn-icon"> <i class="bx bx-edit"></i></a>
                                                <form action="{{ route($routePrefix . '.destroy', $siswa->id) }}"
                                                    method="post" class="d-inline">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="btn btn-sm btn-danger btn-icon"
                                                        onclick="return confirm('Apakah anda yakin?')"><i class="bx bx-trash">
                                                    </i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
@endsection

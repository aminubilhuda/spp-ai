@extends('layouts.app_sneat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $title }}</h5>
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="card student-card border-primary">
                                    <div class="card-header bg-primary text-white text-center">
                                        <h5>KARTU PELAJAR</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="avatar avatar-xl mb-3">
                                            @if ($model->foto)
                                                <img src="{{ asset('storage/' . $model->foto) }}"
                                                    alt="Foto {{ $model->nama }}" class="rounded-circle">
                                            @else
                                                @if ($model->jenis_kelamin == 'Laki-laki')
                                                    <img src="{{ asset('sneat/assets/img/avatars/male-student.png') }}"
                                                        alt="Avatar" class="rounded-circle">
                                                @else
                                                    <img src="{{ asset('sneat/assets/img/avatars/female-student.png') }}"
                                                        alt="Avatar" class="rounded-circle">
                                                @endif
                                            @endif
                                        </div>
                                        <h5 class="mb-1">{{ $model->nama }}</h5>
                                        <p class="mb-0">NISN: {{ $model->nisn }}</p>
                                        <p class="mb-0">NIS: {{ $model->nis }}</p>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <p class="mb-0">{{ $model->jurusan->nama }} - Kelas {{ $model->kelas }}</p>
                                        <p class="mb-0">Angkatan: {{ $model->angkatan }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">INFORMASI LENGKAP</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="30%"><strong>Nama Lengkap</strong></td>
                                                <td width="5%">:</td>
                                                <td>{{ $model->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>NISN</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->nisn }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>NIS</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->nis }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jenis Kelamin</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->jenis_kelamin }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jurusan</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->jurusan->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kelas</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->kelas }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Angkatan</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->angkatan }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div
                                        class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">INFORMASI WALI</h5>
                                        @if ($model->wali_id)
                                            <a href="{{ route('siswa.wali', $model->id) }}" class="btn btn-light btn-sm">
                                                <i class="fas fa-eye"></i> Detail Wali Murid
                                            </a>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="30%"><strong>Nama Wali</strong></td>
                                                <td width="5%">:</td>
                                                <td>{{ $model->wali->name ?? 'Belum diatur' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status Wali</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->wali_status ?? 'Belum diatur' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email Wali</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->wali->email ?? 'Belum diatur' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. HP</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->wali->nohp ?? 'Belum diatur' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <a href="{{ route($routePrefix . '.edit', $model->id) }}" class="btn btn-warning">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form action="{{ route($routePrefix . '.destroy', $model->id) }}" method="post"
                                    class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Apakah anda yakin?')">
                                        <i class="fa fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .student-card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            border-radius: 10px;
            overflow: hidden;
        }

        .student-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .student-card .card-header {
            font-weight: bold;
            letter-spacing: 1px;
        }

        .avatar img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>
@endsection

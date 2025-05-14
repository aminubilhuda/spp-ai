@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <form action="{{ route($routePrefix . '.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h6 class="fw-semibold">Petunjuk Import Data Siswa</h6>
                                <ul>
                                    <li>Silahkan download template Excel import
                                        <a href="{{ route($routePrefix . '.import.template') }}"
                                            class="btn btn-sm btn-primary">
                                            <i class='bx bx-download'></i> Download Template
                                        </a>
                                    </li>
                                    <li>Isi data sesuai dengan kolom yang ada di template</li>
                                    <li>Kolom yang wajib diisi: nama, nisn, kelas, angkatan</li>
                                    <li>Kolom jurusan diisi dengan nama jurusan yang ada di database</li>
                                    <li>Alamat dan nomor telepon boleh dikosongkan</li>
                                    <li>Setelah selesai mengisi, upload file Excel tersebut melalui form di bawah</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="file" class="form-label">File Excel</label>
                            <input type="file" name="file" id="file" class="form-control">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-upload'></i> Import Data
                            </button>
                            <a href="{{ route($routePrefix . '.index') }}" class="btn btn-danger">
                                <i class='bx bx-x'></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

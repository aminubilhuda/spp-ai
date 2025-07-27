@extends('layouts.app_sneat', ['title' => 'Detail Wali Murid'])

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/operator/wali_detail.css') }}">
@endsection
    @section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="col-md-10">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $title }}</h5>
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary btn-sm btn-icon">
                            <i class="bx bx-arrow-back"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center mb-4">
                                <div class="card guardian-info-card">
                                    <div class="card-header text-center">
                                        INFORMASI WALI MURID
                                    </div>
                                    <div class="card-body">
                                        <div class="avatar avatar-xxl mb-3">
                                            <img src="{{ asset('sneat/assets/img/avatars/1.png') }}" alt="Avatar"
                                                class="rounded-circle">
                                        </div>
                                        <h5 class="mb-1">{{ $model->name }}</h5>
                                        <p class="mb-0 badge bg-label-primary">
                                            Wali Murid
                                        </p>
                                    </div>
                                    <div class="card-footer">
                                        <p class="mb-0"><i class="bx bx-envelope me-1"></i> {{ $model->email }}</p>
                                        <p class="mb-0"><i class="bx bx-phone me-1"></i> {{ $model->nohp ?? 'Tidak ada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card detail-info-card mb-3">
                                    <div class="card-header">
                                        INFORMASI LENGKAP WALI MURID
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Nama Lengkap</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->email }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. HP</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->nohp ?? 'Tidak tersedia' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Peran</strong></td>
                                                <td>:</td>
                                                <td>{{ ucfirst($model->akses) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Terdaftar Sejak</strong></td>
                                                <td>:</td>
                                                <td>{{ $model->created_at->format('d F Y') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Add Student to Guardian Form -->
                                <div class="card add-student-card mb-3">
                                    <div class="card-header">
                                        TAMBAH SISWA KE WALI MURID
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('siswa.tambahkewali') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="wali_id" value="{{ $model->id }}">
                                            <div class="mb-3">
                                                <label for="siswa_id" class="form-label">Pilih Siswa</label>
                                                <select name="siswa_id" id="siswa_id" class="form-select select2">
                                                    <option value="">-- Pilih Siswa --</option>
                                                    @php
                                                        $availableSiswa = \App\Models\Siswa::whereNull('wali_id')
                                                            ->orWhere('wali_id', 0)
                                                            ->orderBy('nama')
                                                            ->get();
                                                    @endphp
                                                    @foreach ($availableSiswa as $s)
                                                        <option value="{{ $s->id }}">{{ $s->nama }} - NISN:
                                                            {{ $s->nisn }} - {{ $s->kelas }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="wali_status" class="form-label">Status Wali</label>
                                                <select name="wali_status" id="wali_status" class="form-select">
                                                    <option value="Ayah">Ayah</option>
                                                    <option value="Ibu">Ibu</option>
                                                    <option value="Wali">Wali Lainnya</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bx bx-user-plus me-1"></i> Tambahkan Siswa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Terkait Section -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card related-students-card">
                                    <div class="card-header">
                                        SISWA YANG DIASUH
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Nama Siswa</th>
                                                        <th>NISN</th>
                                                        <th>Kelas</th>
                                                        <th>Jurusan</th>
                                                        <th>Status Wali</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($siswaList as $siswa)
                                                        <tr>
                                                            <td>{{ $siswa->nama }}</td>
                                                            <td>{{ $siswa->nisn }}</td>
                                                            <td>{{ $siswa->kelas }}</td>
                                                            <td>{{ $siswa->jurusan->nama }}</td>
                                                            <td><span
                                                                    class="badge bg-label-primary">{{ $siswa->wali_status ?? 'Wali' }}</span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('siswa.show', $siswa->id) }}"
                                                                    class="btn btn-sm btn-info btn-icon">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                                <form action="{{ route('siswa.hapusdariwall') }}"
                                                                    method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="siswa_id"
                                                                        value="{{ $siswa->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-danger btn-icon"
                                                                        onclick="return confirm('Apakah yakin ingin menghapus siswa ini dari wali murid?')">
                                                                        <i class="bx bx-unlink"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">
                                                                <div class="alert alert-info mt-2">
                                                                    <i class="bx bx-info-circle me-2"></i>
                                                                    Belum ada siswa yang diasuh oleh wali murid ini
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <a href="{{ route($routePrefix . '.edit', $model->id) }}" class="btn btn-warning">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <form action="{{ route($routePrefix . '.destroy', $model->id) }}" method="post"
                                    class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "Pilih siswa yang akan ditambahkan",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
    @endpush
@endsection
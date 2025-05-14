@extends('layouts.app_sneat')

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
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="card guardian-card border-primary">
                                    <div class="card-header bg-primary text-white text-center">
                                        <h5>INFORMASI WALI MURID</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="avatar avatar-xl mb-3">
                                            <img src="{{ asset('sneat/assets/img/avatars/1.png') }}" alt="Avatar"
                                                class="rounded-circle">
                                        </div>
                                        <h5 class="mb-1">{{ $model->name }}</h5>
                                        <p class="mb-0 badge bg-label-primary">
                                            Wali Murid
                                        </p>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <p class="mb-0"><i class="bx bx-envelope"></i> {{ $model->email }}</p>
                                        <p class="mb-0"><i class="bx bx-phone"></i> {{ $model->nohp ?? 'Tidak ada' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">INFORMASI LENGKAP WALI MURID</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="30%"><strong>Nama Lengkap</strong></td>
                                                <td width="5%">:</td>
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

                                <!-- Add Student to Guardian Form -->
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">TAMBAH SISWA KE WALI MURID</h5>
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
                                                <i class="fas fa-user-plus"></i> Tambahkan Siswa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Terkait Section -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">SISWA YANG DIASUH</h5>
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
                                                                    class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> Detail
                                                                </a>
                                                                <form action="{{ route('siswa.hapusdariwall') }}"
                                                                    method="POST" class="d-inline"
                                                                    onsubmit="return confirm('Apakah yakin ingin menghapus siswa ini dari wali murid?')">
                                                                    @csrf
                                                                    <input type="hidden" name="siswa_id"
                                                                        value="{{ $siswa->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                                        <i class="fas fa-unlink"></i> Hapus dari Wali
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">
                                                                <div class="alert alert-info mt-2">
                                                                    <i class="fas fa-info-circle me-2"></i>
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
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form action="{{ route($routePrefix . '.destroy', $model->id) }}" method="post"
                                    class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Apakah anda yakin ingin menghapus wali murid ini?')">
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
        .guardian-card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            border-radius: 10px;
            overflow: hidden;
        }

        .guardian-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .guardian-card .card-header {
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

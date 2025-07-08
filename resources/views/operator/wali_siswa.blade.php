@extends('layouts.app_sneat', ['title' => $title])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <div>
                        <a href="{{ route('wali.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Info Wali -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="120"><strong>Nama Wali:</strong></td>
                                    <td>{{ $wali->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $wali->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>No. HP:</strong></td>
                                    <td>{{ $wali->nohp }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Total Siswa: {{ $siswaList->total() }}</h6>
                                <p class="mb-0">Berikut adalah daftar siswa yang terkait dengan wali ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Siswa -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">NISN</th>
                                    <th width="15%">NIS</th>
                                    <th width="25%">Nama Siswa</th>
                                    <th width="15%">Kelas</th>
                                    <th width="15%">Jurusan</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siswaList as $siswa)
                                    <tr>
                                        <td>{{ $loop->iteration + ($siswaList->currentPage() - 1) * $siswaList->perPage() }}</td>
                                        <td>{{ $siswa->nisn }}</td>
                                        <td>{{ $siswa->nis }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        <td>{{ $siswa->kelas }}</td>
                                        <td>{{ $siswa->jurusan->nama ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('siswa.show', $siswa->id) }}" 
                                               class="btn btn-info btn-sm" 
                                               title="Detail Siswa">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('tagihan.showByStudent', $siswa->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               title="Lihat Tagihan">
                                                <i class="bx bx-receipt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-user-x bx-lg mb-2"></i>
                                                <p class="mb-0">Belum ada siswa yang terkait dengan wali ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($siswaList->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $siswaList->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection 
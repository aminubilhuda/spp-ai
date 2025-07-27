@extends('layouts.app_sneat', ['title' => 'User'])

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
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
                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">
                    <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary mb-3 btn-sm">Tambah User</a>
                    <div class="table-responsive">
                        <table class="table table-striped" id="myTable">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama</td>
                                    <td>No Hp</td>
                                    <td>Email</td>
                                    <td>Akses</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                                                @forelse ($models as $user)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->nohp }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->akses }}</td>
                                        <td>
                                            <a href="{{ route($routePrefix . '.show', $user->id) }}"
                                                class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route($routePrefix . '.edit', $user->id) }}"
                                                class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if($routePrefix === 'wali')
                                                <!-- Tombol Reset Password - khusus untuk wali -->
                                                <a href="{{ route('wali.reset-password', $user->id) }}"
                                                   class="btn btn-sm btn-secondary" 
                                                   title="Reset Password"
                                                   onclick="return confirm('Reset password wali {{ $user->name }}?')">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                
                                                <!-- Tombol Lihat Siswa - khusus untuk wali -->
                                                <a href="{{ route('wali.siswa', $user->id) }}"
                                                   class="btn btn-sm btn-primary" 
                                                   title="Lihat Siswa">
                                                    <i class="fas fa-users"></i>
                                                    <span class="badge bg-light text-dark">{{ $user->siswa->count() }}</span>
                                                </a>
                                            @endif
                                            
                                            <form action="{{ route($routePrefix . '.destroy', $user->id) }}" method="post"
                                                class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Apakah anda yakin ingin menghapus {{ $user->name }}?')" 
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
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

@section('scripts')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
@endsection

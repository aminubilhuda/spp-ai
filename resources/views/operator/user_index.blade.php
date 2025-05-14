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
                    <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary mb-3 btn-sm">Tambah User</a>
                    <div class="table-responsive">
                        <table class="table table-striped">
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
                                                class="btn btn-sm btn-info"> <i class="fas fa-eye"></i> Detail</a>
                                            <a href="{{ route($routePrefix . '.edit', $user->id) }}"
                                                class="btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Edit</a>
                                            <form action="{{ route($routePrefix . '.destroy', $user->id) }}" method="post"
                                                class="d-inline">
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
                                        <td colspan="6" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="card-footer">
                            {{ $models->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

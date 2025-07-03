@extends('layouts.app_sneat')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Data Biaya</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <a href="{{ route('biaya.create') }}" class="btn btn-primary btn-sm float-end">
                                <i class="menu-icon tf-icons bx bx-plus"></i> Tambah Biaya
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
                                    <th>Nama Biaya</th>
                                    <th>Tipe</th>
                                    <th>Total Tagihan</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($models as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $item->nama }}</strong>
                                            @if($item->isParent() && $item->children->count() > 0)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $item->children->count() }} komponen biaya
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->isParent())
                                                <span class="badge bg-primary">Parent</span>
                                            @else
                                                <span class="badge bg-secondary">Child</span>
                                            @endif
                                        </td>
                                        <td>{{ formatRupiah($item->total_tagihan) }}</td>
                                        <td>{{ $item->user->name ?? 'Tidak Diketahui' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('biaya.show', $item->id) }}"
                                                    class="btn btn-info btn-sm me-2">
                                                    <i class="menu-icon tf-icons bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('biaya.edit', $item->id) }}"
                                                    class="btn btn-warning btn-sm me-2">
                                                    <i class="menu-icon tf-icons bx bx-edit"></i>
                                                </a>
                                                @if($item->isParent())
                                                    <a href="{{ route('biaya.create', ['parent_id' => $item->id]) }}"
                                                        class="btn btn-success btn-sm me-2" title="Tambah Komponen">
                                                        <i class="menu-icon tf-icons bx bx-plus"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('biaya.destroy', $item->id) }}" method="POST"
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
                                    
                                    {{-- Tampilkan children di bawah parent --}}
                                    @if($item->isParent() && $item->children->count() > 0)
                                        @foreach($item->children as $child)
                                            <tr class="table-light">
                                                <td></td>
                                                <td class="ps-4">
                                                    <i class="bx bx-right-arrow-alt"></i>
                                                    {{ $child->nama }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">Child</span>
                                                </td>
                                                <td>{{ formatRupiah($child->jumlah) }}</td>
                                                <td>{{ $child->user->name ?? 'Tidak Diketahui' }}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ route('biaya.edit', $child->id) }}"
                                                            class="btn btn-warning btn-sm me-2">
                                                            <i class="menu-icon tf-icons bx bx-edit"></i>
                                                        </a>
                                                        <form action="{{ route('biaya.destroy', $child->id) }}" method="POST"
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
                                        @endforeach
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
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

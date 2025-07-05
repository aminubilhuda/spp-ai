@extends('layouts.app_sneat', ['title' => 'Biaya'])

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <form action="{{ route($action, $model->exists ? $model->id : '') }}" method="POST">
                        @csrf
                        @if ($method == 'PUT')
                            @method('PUT')
                        @endif

                        @if (request()->filled('parent_id'))
                            <h1>Item {{ $parentData->nama }}</h1>
                            <input type="hidden" name="parent_id" value="{{ $parentData->id }}">
                            <div class="col-md-6">
                                <table class="table table-hover table-sm">
                                    <thead>
                                    <tr>
                                        <th width="7%">Parent ID</th>
                                        <th>Nama Biaya</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($parentData->children as $item)
                                        <tr>
                                            <td>{{ $item->parent_id }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td>{{ formatRupiah($item->total_tagihan) }}</td>
                                            <td>
                                                <a href="{{ route('biaya.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                                    <i class="menu-icon tf-icons bx bx-edit"></i>
                                                </a>
                                                <a href="{{ route('delete-biaya.item', $item->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="menu-icon tf-icons bx bx-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label for="nama">Nama Biaya</label>
                            <input type="text" name="nama" id="nama" class="form-control"
                                value="{{ old('nama', $model->nama) }}" autofocus>
                            <span class="text-danger">{{ $errors->first('nama') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="jumlah">Jumlah (Rp)</label>
                            <input type="text" name="jumlah" id="jumlah" class="form-control rupiah"
                                value="{{ old('jumlah', $model->jumlah) }}">
                            <span class="text-danger">{{ $errors->first('jumlah') }}</span>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ $button }}</button>
                            <a href="{{ route('biaya.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

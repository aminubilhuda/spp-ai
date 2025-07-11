@extends('layouts.app_sneat')

@section('content')
<div class="row justify-content-center">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">{{ $title ?? 'Histori Pengeluaran Kas' }}</h5>
            <div class="card-body">
                <a href="{{ route('pengeluaran-kas.create') }}" class="btn btn-primary mb-3 btn-sm">+ Tambah Pengeluaran</a>
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}" placeholder="Dari tanggal">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}" placeholder="Sampai tanggal">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="kategori" class="form-control" value="{{ request('kategori') }}" placeholder="Kategori (opsional)">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary w-100">Filter</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Operator</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = ($pengeluaran->currentPage() - 1) * $pengeluaran->perPage() + 1; @endphp
                            @forelse($pengeluaran as $item)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>Rp {{ number_format($item->jumlah,0,',','.') }}</td>
                                <td>{{ $item->kategori }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>{{ $item->user->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data pengeluaran</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $pengeluaran->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 
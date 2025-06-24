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
                    <!-- Filter Form -->
                    <form action="{{ route('pembayaran.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Cari Siswa</label>
                                <input type="text" name="search" class="form-control" placeholder="Nama/NISN siswa"
                                    value="{{ $search ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status Konfirmasi</label>
                                <select name="status_konfirmasi" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="Belum Dikonfirmasi"
                                        {{ $status_konfirmasi == 'Belum Dikonfirmasi' ? 'selected' : '' }}>Belum
                                        Dikonfirmasi</option>
                                    <option value="Sudah Dikonfirmasi"
                                        {{ $status_konfirmasi == 'Sudah Dikonfirmasi' ? 'selected' : '' }}>Sudah
                                        Dikonfirmasi</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="form-select">
                                    <option value="">Semua Metode</option>
                                    <option value="Bank Transfer"
                                        {{ $metode_pembayaran == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Cash" {{ $metode_pembayaran == 'Cash' ? 'selected' : '' }}>Tunai
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control"
                                    value="{{ $tanggal_dari ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control"
                                    value="{{ $tanggal_sampai ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Pembayaran</h6>
                                    <h4>{{ $pembayaran->total() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Sudah Dikonfirmasi</h6>
                                    <h4>{{ $pembayaran->where('status_konfirmasi', 'Sudah Dikonfirmasi')->count() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Belum Dikonfirmasi</h6>
                                    <h4>{{ $pembayaran->where('status_konfirmasi', 'Belum Dikonfirmasi')->count() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Nilai</h6>
                                    <h4>{{ formatRupiah($pembayaran->sum('jumlah_dibayar')) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Siswa</th>
                                    <th>Item Tagihan</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Bank Sekolah</th>
                                    <th>Status Konfirmasi</th>
                                    <th>Dikonfirmasi Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse ($pembayaran as $item)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>
                                            <strong>{{ $item->tagihan->siswa->nama ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $item->tagihan->siswa->nisn ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $item->tagihan_detail->nama_biaya ?? 'N/A' }}</td>
                                        <td>{{ formatRupiah($item->jumlah_dibayar) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-label-{{ $item->metode_pembayaran == 'Bank Transfer' ? 'info' : 'success' }}">
                                                {{ $item->metode_pembayaran }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->bank_sekolah)
                                                <span class="badge bg-label-info">
                                                    {{ $item->bank_sekolah->nama_bank }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->status_konfirmasi == 'Sudah Dikonfirmasi')
                                                <span class="badge bg-label-success">Sudah Dikonfirmasi</span>
                                            @else
                                                <span class="badge bg-label-warning">Belum Dikonfirmasi</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->user)
                                                {{ $item->user->name }}<br>
                                                <small
                                                    class="text-muted">{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') : '-' }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @if ($item->bukti_bayar)
                                                    <a href="{{ Storage::url($item->bukti_bayar) }}" target="_blank"
                                                        class="btn btn-sm btn-info" title="Lihat Bukti">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif

                                                @if ($item->status_konfirmasi == 'Belum Dikonfirmasi')
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        onclick="confirmPayment({{ $item->id }})"
                                                        title="Konfirmasi Pembayaran">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif

                                                <a href="{{ route('kwitansi_pembayaran.show', $item->id) }}"
                                                    class="btn btn-sm btn-primary" title="Cetak Kwitansi">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data pembayaran</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $pembayaran->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmPayment(paymentId) {
            if (confirm('Apakah Anda yakin ingin mengkonfirmasi pembayaran ini?')) {
                fetch(`/operator/pembayaran/${paymentId}/confirm`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal mengkonfirmasi pembayaran: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengkonfirmasi pembayaran');
                    });
            }
        }
    </script>
@endpush

@extends('layouts.app_sneat', ['title' => 'Siswa'])

@section('content')

    <div class="row justify-content-center">
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
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    @if($model->status == \App\Models\Siswa::STATUS_AKTIF)
                        <a href="{{ route('status.update', ['model' => 'Siswa', 'id' => $model->id, 'status' => \App\Models\Siswa::STATUS_NONAKTIF]) }}" class="btn btn-danger btn-sm">Non-Aktifkan Siswa ini</a>
                    @else
                        <a href="{{ route('status.update', ['model' => 'Siswa', 'id' => $model->id, 'status' => \App\Models\Siswa::STATUS_AKTIF]) }}" class="btn btn-success btn-sm">Aktifkan Siswa ini</a>
                    @endif
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <!-- Student Photo and Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            @if ($model->foto)
                                <img src="{{ asset('storage/' . $model->foto) }}"
                                    alt="Foto {{ $model->nama }}" class="img-fluid rounded" style="max-width: 150px;">
                            @else
                                <div class="bg-light rounded p-4" style="width: 150px; height: 150px; margin: 0 auto;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $model->nama }}</h4>
                            <p class="text-muted mb-2">NISN: {{ $model->nisn }} | NIS: {{ $model->nis }}</p>
                            <p class="text-muted mb-0">{{ $model->jurusan->nama }} - Kelas {{ $model->kelas }} | Angkatan {{ $model->angkatan }}</p>
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Informasi Siswa</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="40%"><strong>Status</strong></td>
                                            <td>
                                                @if($model->status == 'Aktif')
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="40%"><strong>Nama Lengkap</strong></td>
                                            <td>{{ $model->nama }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NISN</strong></td>
                                            <td>{{ $model->nisn }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NIS</strong></td>
                                            <td>{{ $model->nis }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis Kelamin</strong></td>
                                            <td>{{ $model->jenis_kelamin }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jurusan</strong></td>
                                            <td>{{ $model->jurusan->nama }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kelas</strong></td>
                                            <td>{{ $model->kelas }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Angkatan</strong></td>
                                            <td>{{ $model->angkatan }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Informasi Wali</h6>
                                    @if ($model->wali_id)
                                        <a href="{{ route('siswa.wali', $model->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="40%"><strong>Nama Wali</strong></td>
                                            <td>{{ $model->wali->name ?? 'Belum diatur' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status Wali</strong></td>
                                            <td>{{ $model->wali_status ?? 'Belum diatur' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>{{ $model->wali->email ?? 'Belum diatur' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. HP</strong></td>
                                            <td>{{ $model->wali->nohp ?? 'Belum diatur' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Biaya Siswa -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Detail Biaya Siswa</h6>
                                    <div>
                                        <a href="{{ route('tagihan.create') }}?siswa_id={{ $model->id }}" class="btn btn-success btn-sm me-2">
                                            <i class="fas fa-plus"></i> Buat Tagihan
                                        </a>
                                        <a href="{{ route('tagihan.showByStudent', $model->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Lihat Tagihan Lengkap
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($model->tagihan->count() > 0)
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td width="40%"><strong>Total Tagihan</strong></td>
                                                        <td>{{ formatRupiah($model->total_tagihan) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total Pembayaran</strong></td>
                                                        <td>{{ formatRupiah($model->total_pembayaran) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Sisa Tagihan</strong></td>
                                                        <td>{{ formatRupiah($model->total_tagihan - $model->total_pembayaran) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Jumlah Tagihan</strong></td>
                                                        <td>{{ $model->tagihan->count() }} tagihan</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Ringkasan Tagihan:</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($model->tagihan->take(3) as $tagihan)
                                                        <li class="mb-2">
                                                            <strong>{{ $tagihan->tanggal_tagihan ? \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d/m/Y') : 'N/A' }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                Total: {{ formatRupiah($tagihan->tagihan_details->sum('jumlah_biaya')) }}
                                                                | Jatuh Tempo: {{ $tagihan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d/m/Y') : 'N/A' }}
                                                            </small>
                                                        </li>
                                                    @endforeach
                                                    @if($model->tagihan->count() > 3)
                                                        <li class="text-muted">... dan {{ $model->tagihan->count() - 3 }} tagihan lainnya</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Tabel Detail Tagihan -->
                                        <div class="table-responsive">
                                            <h6 class="mb-3">Rincian Tagihan Detail:</h6>
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal Tagihan</th>
                                                        <th>Nama Biaya</th>
                                                        <th>Jumlah</th>
                                                        <th>Dibayar</th>
                                                        <th>Sisa</th>
                                                        <th>Status</th>
                                                        <th>Jatuh Tempo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $no = 1; @endphp
                                                    @foreach($model->tagihan->take(10) as $tagihan)
                                                        @foreach($tagihan->tagihan_details as $detail)
                                                            @php
                                                                $totalBayar = $detail->pembayaran
                                                                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                                                    ->sum('jumlah_dibayar');
                                                                $sisaTagihan = $detail->jumlah_biaya - $totalBayar;
                                                                
                                                                if ($sisaTagihan <= 0) {
                                                                    $statusDisplay = 'lunas';
                                                                    $statusClass = 'success';
                                                                } elseif ($totalBayar > 0) {
                                                                    $statusDisplay = 'angsur';
                                                                    $statusClass = 'warning';
                                                                } else {
                                                                    $statusDisplay = 'belum_lunas';
                                                                    $statusClass = 'danger';
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $no++ }}</td>
                                                                <td>{{ $tagihan->tanggal_tagihan ? \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d/m/Y') : '-' }}</td>
                                                                <td>
                                                                    <strong>{{ $detail->nama_biaya }}</strong>
                                                                    @if($tagihan->keterangan)
                                                                        <br><small class="text-muted">{{ $tagihan->keterangan }}</small>
                                                                    @endif
                                                                </td>
                                                                <td>{{ formatRupiah($detail->jumlah_biaya) }}</td>
                                                                <td>{{ formatRupiah($totalBayar) }}</td>
                                                                <td>{{ formatRupiah($sisaTagihan) }}</td>
                                                                <td>
                                                                    @if($statusDisplay == 'lunas')
                                                                        <span class="badge bg-success">Lunas</span>
                                                                    @elseif($statusDisplay == 'angsur')
                                                                        <span class="badge bg-warning">Angsur</span>
                                                                    @else
                                                                        <span class="badge bg-danger">Belum Lunas</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        $jatuhTempo = \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo);
                                                                        $now = \Carbon\Carbon::now();
                                                                        $isTerlambat = $now->gt($jatuhTempo) && $sisaTagihan > 0;
                                                                    @endphp
                                                                    <span class="{{ $isTerlambat ? 'text-danger' : '' }}">
                                                                        {{ $jatuhTempo->format('d/m/Y') }}
                                                                        @if($isTerlambat)
                                                                            <br><small class="text-danger">Terlambat</small>
                                                                        @endif
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        @if($model->tagihan->count() > 10)
                                            <div class="text-center mt-3">
                                                <small class="text-muted">
                                                    Menampilkan 10 tagihan terbaru dari {{ $model->tagihan->count() }} tagihan
                                                </small>
                                            </div>
                                        @endif

                                    @else
                                        <div class="text-center text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <p>Belum ada tagihan untuk siswa ini</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Pembayaran Terbaru -->
                    @php
                        $pembayaranTerbaru = collect();
                        foreach($model->tagihan as $tagihan) {
                            foreach($tagihan->tagihan_details as $detail) {
                                foreach($detail->pembayaran as $pembayaran) {
                                    $pembayaranTerbaru->push($pembayaran);
                                }
                            }
                        }
                        $pembayaranTerbaru = $pembayaranTerbaru->sortByDesc('created_at')->take(5);
                    @endphp

                    @if($pembayaranTerbaru->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Riwayat Pembayaran Terbaru</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Nama Biaya</th>
                                                        <th>Jumlah Bayar</th>
                                                        <th>Metode</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $no = 1; @endphp
                                                    @foreach($pembayaranTerbaru as $pembayaran)
                                                        <tr>
                                                            <td>{{ $no++ }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d/m/Y') }}</td>
                                                            <td>{{ $pembayaran->tagihan_detail->nama_biaya ?? 'N/A' }}</td>
                                                            <td>{{ formatRupiah($pembayaran->jumlah_dibayar) }}</td>
                                                            <td>{{ $pembayaran->metode_pembayaran }}</td>
                                                            <td>
                                                                @if($pembayaran->status_konfirmasi == 'Sudah Dikonfirmasi')
                                                                    <span class="badge bg-success">Dikonfirmasi</span>
                                                                @else
                                                                    <span class="badge bg-warning">Menunggu</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <a href="{{ route($routePrefix . '.edit', $model->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="{{ route($routePrefix . '.destroy', $model->id) }}" method="post" class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah anda yakin?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
@endsection

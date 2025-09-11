@extends('layouts.app_sneat', ['title' => 'Tagihan'])

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/operator/tagihan_index.css') }}">
@endsection

@section('content')
    <div class="row justify-content-center">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Summary Cards -->
        <div class="row mb-4">
            {{-- <div class="col-lg-4 col-md-6 mb-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total Tagihan</h5>
                                <small class="text-muted">Seluruh Siswa</small>
                                <h3 class="mt-2 mb-0">{{ $models->count() }}</h3>
                            </div>
                            <div class="avatar bg-label-primary p-2 rounded-circle">
                                <i class="bx bx-file text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total Tagihan</h5>
                                <small class="text-muted">Data Terfilter</small>
                                <h3 class="mt-2 mb-0">{{ formatRupiah($models->sum('total_nilai')) }}</h3>
                            </div>
                            <div class="avatar bg-label-success p-2 rounded-circle">
                                <i class="bx bx-money text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total Tagihan</h5>
                                <small class="text-muted">Tahun Pelajaran {{ $tahunAktif?->nama ?? '-' }}</small>
                                <h3 class="mt-2 mb-0">{{ formatRupiah($totalTagihanSetahun) }}</h3>
                            </div>
                            <div class="avatar bg-label-info p-2 rounded-circle">
                                <i class="bx bx-calendar text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Tagihan Belum Lunas</h5>
                                <small class="text-muted">Data Terfilter</small>
                                <h3 class="mt-2 mb-0">{{ formatRupiah($models->filter(fn($item) => $item->status !== 'lunas')->sum('total_nilai')) }}</h3>
                            </div>
                            <div class="avatar bg-label-warning p-2 rounded-circle">
                                <i class="bx bx-hourglass text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Sisa Tagihan</h5>
                                <small class="text-muted">Tahun Pelajaran {{ $tahunAktif?->nama ?? '-' }}</small>
                                <h3 class="mt-2 mb-0">{{ formatRupiah($sisaTagihanSetahun) }}</h3>
                            </div>
                            <div class="avatar bg-label-danger p-2 rounded-circle">
                                <i class="bx bx-wallet text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    {{ $title }}
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm me-2" data-bs-toggle="modal"
                            data-bs-target="#deleteModal">
                            <i class="bx bx-trash me-1"></i> Hapus Berdasarkan Filter
                        </button>
                        <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Tambah Tagihan
                        </a>
                    </div>
                </h5>

                <div class="card-body">
                    <!-- Search Section -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-6">
                            <form action="{{ route($routePrefix . '.index') }}" method="GET" class="d-flex align-items-center gap-2">
                                <div class="input-group search-box flex-grow-1">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari siswa berdasarkan nama/NISN" value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bx bx-search"></i>
                                    </button>
                                    @if (request('search'))
                                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-x"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3 ms-auto">
                            <form action="{{ route($routePrefix . '.index') }}" method="GET">
                                <select name="tahun_pelajaran_id" class="form-select" onchange="this.form.submit()">
                                    @foreach($tahunPelajarans as $tp)
                                        <option value="{{ $tp->id }}" {{ $tp->id == $tahunPelajaranId ? 'selected' : '' }}>
                                            {{ $tp->nama }}{{ $tp->is_aktif ? ' (Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm" id="tagihan-table">
                            <thead>
                                <tr>
                                    <th style="width: 3%">No</th>
                                    <th style="width: 15%">Nama</th>
                                    <th style="width: 10%">NISN</th>
                                    <th style="width: 7%">Kelas</th>
                                    <th style="width: 15%">Jurusan</th>
                                    <th style="width: 10%">Jumlah</th>
                                    <th style="width: 20%">Total & Sisa</th>
                                    <th style="width: 20%" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($models as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold text-primary">
                                            @if ($item->siswa)
                                                {{ $item->siswa->nama }}
                                            @else
                                                <span class="text-muted">Data tidak tersedia</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->siswa)
                                                {{ $item->siswa->nisn }}
                                            @else
                                                <span class="text-danger">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->siswa)
                                                {{ $item->siswa->kelas }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->siswa && $item->siswa->jurusan)
                                                {{ $item->siswa->jurusan->nama }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $item->total_tagihan }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="fw-semibold">{{ formatRupiah($item->total_nilai) }}</span>
                                                <small class="text-muted">
                                                    Sisa: <span class="text-{{ $item->sisa_tagihan > 0 ? 'danger' : 'success' }}">
                                                        {{ formatRupiah($item->sisa_tagihan) }}
                                                    </span>
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route($routePrefix . '.showByStudent', $item->siswa_id) }}"
                                                    class="btn btn-icon btn-sm btn-info" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('kartu.spp', [$item->siswa_id, 'tahun_pelajaran_id' => $tahunPelajaranId]) }}"
                                                    class="btn btn-icon btn-sm btn-primary" target="_blank" title="Cetak Kartu SPP">
                                                    <i class="bx bx-printer"></i>
                                                </a>
                                                <form action="{{ route('tagihan.kirim-wa', ['tagihan' => $item->latest_tagihan_id ?? $item->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-icon btn-sm btn-success" title="Kirim WhatsApp Manual">
                                                        <i class="bx bxl-whatsapp"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route($routePrefix . '.destroy', $item->siswa_id) }}"
                                                    method="POST" onsubmit="return confirm('Yakin ingin menghapus data?')"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-sm btn-danger" title="Hapus">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                
                                @endforeach
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Tagihan Berdasarkan Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route($routePrefix . '.deleteByCategory') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Angkatan</label>
                            <select name="angkatan" class="form-select">
                                <option value="">Pilih Angkatan...</option>
                                @foreach ($angkatan as $angkatanItem)
                                    <option value="{{ $angkatanItem }}">{{ $angkatanItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <select name="jurusan" class="form-select">
                                <option value="">Pilih Jurusan...</option>
                                @foreach ($jurusan as $id => $nama)
                                    <option value="{{ $id }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas" class="form-select">
                                <option value="">Pilih Kelas...</option>
                                @foreach ($kelas as $kelasItem)
                                    <option value="{{ $kelasItem }}">{{ $kelasItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih Jenis Kelamin...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <i class="bx bx-error me-2 bx-sm"></i>
                                <div>
                                    Pastikan filter yang dipilih sudah benar. Data yang dihapus tidak dapat dikembalikan.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Anda yakin ingin menghapus data tagihan dengan kriteria tersebut?')">
                            <i class="bx bx-trash me-1"></i> Hapus Tagihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#tagihan-table').DataTable({
                paging: false,
                ordering: true,
                info: false,
                searching: true,
                columnDefs: [{
                        orderable: false,
                        targets: [7] // Disable sorting on action column
                    }
                ]
            });

            $('.form-control[name="search"]').on('keyup', function() {
                table.search(this.value).draw();
            });

            $('.btn').hover(
                function() {
                    $(this).addClass('shadow-sm');
                },
                function() {
                    $(this).removeClass('shadow-sm');
                }
            );
        });
    </script>
@endsection

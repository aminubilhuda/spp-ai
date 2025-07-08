@extends('layouts.app_sneat', ['title' => 'Tagihan'])

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none;
        }

        .table > :not(caption) > * > * {
            padding: 0.6rem 0.75rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .total-amount {
            font-weight: 600;
            color: #566a7f;
        }

        .search-box {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .search-box .form-control {
            border-radius: 8px 0 0 8px;
            border-right: none;
        }

        .search-box .btn {
            border-radius: 0 8px 8px 0;
        }

        /* Styling untuk filter tahun */
        select.form-select {
            border-radius: 8px;
            border: 1px solid #d9dee3;
            padding: 0.4375rem 2rem 0.4375rem 0.875rem;
            font-size: 0.9375rem;
            font-weight: 400;
            line-height: 1.5;
            color: #566a7f;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23697a8d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.875rem center;
            background-size: 16px 12px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        select.form-select:focus {
            border-color: #696cff;
            box-shadow: 0 0 0.25rem rgba(105, 108, 255, 0.1);
            outline: 0;
        }

        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }

        /* Styling untuk tabel */
        .table thead th {
            background-color: #f5f5f9;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #566a7f;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon i {
            font-size: 1rem;
        }

        .badge.bg-label-primary {
            background-color: #e7e7ff !important;
            color: #696cff;
            font-weight: 500;
        }

        .text-primary {
            color: #696cff !important;
        }

        /* Card styling */
        .card {
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
            border: none;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #d9dee3;
        }
    </style>
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
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title mb-0">Total Tagihan</h5>
                                <small class="text-muted">Seluruh Siswa</small>
                                <h3 class="mt-2 mb-0">{{ $models->count() }}</h3>
                            </div>
                            <div class="avatar bg-primary p-2 rounded">
                                <i class="bx bx-file text-white" style="font-size: 24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title mb-0">Total Nilai</h5>
                                <small class="text-muted">Semua Tagihan</small>
                                <h3 class="mt-2 mb-0">{{ formatRupiah($models->sum('total_nilai')) }}</h3>
                            </div>
                            <div class="avatar bg-success p-2 rounded">
                                <i class="bx bx-money text-white" style="font-size: 24px"></i>
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
                    <div>
                        <button type="button" class="btn btn-danger btn-sm me-2" data-bs-toggle="modal"
                            data-bs-target="#deleteModal">
                            <i class="bx bx-trash"></i> Hapus Berdasarkan Filter
                        </button>
                        <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus"></i> Tambah Tagihan
                        </a>
                    </div>
                </h5>

                <div class="card-body">
                    <!-- Search Section -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form action="{{ route($routePrefix . '.index') }}" method="GET" class="d-flex gap-2">
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
                                <select name="tahun_pelajaran_id" class="form-select" style="width: auto" onchange="this.form.submit()">
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
                                    <th style="width: 10%">Tagihan</th>
                                    <th style="width: 20%">Total</th>
                                    <th style="width: 20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($models as $item)
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
                                            <span class="fw-semibold">{{ formatRupiah($item->total_nilai) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route($routePrefix . '.showByStudent', $item->siswa_id) }}"
                                                    class="btn btn-icon btn-sm btn-info" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('kartu.spp', [$item->siswa_id, 'tahun_pelajaran_id' => $tahunPelajaranId]) }}"
                                                    class="btn btn-icon btn-sm btn-primary" target="_blank" title="Cetak Kartu SPP">
                                                    <i class="bx bx-printer"></i>
                                                </a>
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
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-3">
                                            <div class="text-muted">
                                                <i class="bx bx-folder-open mb-2" style="font-size: 2rem;"></i>
                                                <div>Tidak ada data</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
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
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
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
                            <i class="bx bx-x"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Anda yakin ingin menghapus data tagihan dengan kriteria tersebut?')">
                            <i class="bx bx-trash"></i> Hapus Tagihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script> -->
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables with specific settings
            var table = $('#tagihan-table').DataTable({
                paging: false,
                ordering: true,
                info: false,
                searching: true,
                columnDefs: [{
                        orderable: false,
                        targets: [4]
                    } // Disable sorting on action column
                ]
            });

            // Custom search handler
            $('.form-control[name="search"]').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Animated hover effect for action buttons
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

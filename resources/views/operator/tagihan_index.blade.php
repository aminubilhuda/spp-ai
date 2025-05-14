@extends('layouts.app_sneat')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none;
        }

        .status-badge {
            font-size: 0.85em;
            padding: 5px 10px;
        }

        .summary-card {
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
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
                <div class="card summary-card">
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
                <div class="card summary-card">
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
                        <div class="col-md-12">
                            <form action="{{ route($routePrefix . '.index') }}" method="GET">
                                <div class="input-group">
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
                    </div>

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tagihan-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th style="width: 35%">Nama Siswa</th>
                                    <th style="width: 15%">Total Tagihan</th>
                                    <th style="width: 25%">Total Nilai Tagihan</th>
                                    <th style="width: 20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($models as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                @if ($item->siswa)
                                                    <h6 class="mb-0">{{ $item->siswa->nama }}</h6>
                                                    <small class="text-muted">
                                                        NISN: {{ $item->siswa->nisn }}
                                                        <br>
                                                        <span class="badge bg-label-info">
                                                            {{ optional($item->siswa->jurusan)->nama ?? 'Jurusan tidak tersedia' }}
                                                            - {{ $item->siswa->kelas }}
                                                        </span>
                                                    </small>
                                                @else
                                                    <h6 class="mb-0 text-muted">Data siswa tidak tersedia</h6>
                                                    <small class="text-danger">ID Siswa: {{ $item->siswa_id }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $item->total_tagihan }}</span>
                                        </td>
                                        <td>
                                            <h6 class="mb-0">{{ formatRupiah($item->total_nilai) }}</h6>
                                        </td>
                                        <td>
                                            <form action="{{ route($routePrefix . '.destroy', $item->siswa_id) }}"
                                                method="POST" onsubmit="return confirm('Yakin ingin menghapus data?')"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <a href="{{ route($routePrefix . '.showByStudent', $item->siswa_id) }}"
                                                    class="btn btn-info btn-sm">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data</td>
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
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
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

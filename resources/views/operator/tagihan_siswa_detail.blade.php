@extends('layouts.app_sneat', ['title' => 'Tagihan Siswa'])

@section('styles')
    <style>
        .card-spp {
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card-spp-header {
            background-color: #f5f5f9;
            padding: 10px 15px;
            border-bottom: 1px solid #ccc;
            border-radius: 8px 8px 0 0;
        }

        .card-spp-body {
            padding: 15px;
        }

        .month-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            position: relative;
        }

        .month-box.paid {
            border-color: #71dd37;
            background-color: #f0f9e8;
        }

        .month-box.paid:after {
            content: "✓";
            position: absolute;
            top: 8px;
            right: 10px;
            color: #71dd37;
            font-weight: bold;
        }

        .month-box.partial {
            border-color: #ffab00;
            background-color: #fff8e8;
        }

        .month-box.partial:after {
            content: "⌛";
            position: absolute;
            top: 8px;
            right: 10px;
            color: #ffab00;
        }

        .month-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .month-amount {
            font-size: 14px;
        }

        .month-status {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <a href="{{ route('tagihan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="width: 30%">Nama Siswa</td>
                                    <td>: <strong>{{ $siswa->nama }}</strong></td>
                                </tr>
                                <tr>
                                    <td>NISN</td>
                                    <td>: {{ $siswa->nisn }}</td>
                                </tr>
                                <tr>
                                    <td>Kelas</td>
                                    <td>: {{ $siswa->kelas }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="width: 30%">Angkatan</td>
                                    <td>: {{ $siswa->angkatan }}</td>
                                </tr>
                                <tr>
                                    <td>Jurusan</td>
                                    <td>: {{ $siswa->jurusan->nama ?? 'Data tidak tersedia' }}</td>
                                </tr>
                                <tr>
                                    <td>Jumlah Tagihan</td>
                                    <td>: <span class="badge bg-primary">@php
                                        $totalTagihanDetails = 0;
                                        foreach ($tagihan as $item) {
                                            $totalTagihanDetails += $item->tagihan_details->count();
                                        }
                                        echo $totalTagihanDetails;
                                    @endphp</span> Tagihan</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu SPP -->
            <div class="card mb-4">
                <h5 class="card-header">Kartu SPP Tahun {{ date('Y') }}</h5>
                <div class="card-body">
                    @php
                        $namaBulan = [
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ];

                        // Mengelompokkan tagihan berdasarkan bulan
                        $tagihanByBulan = [];
                        foreach ($tagihan as $item) {
                            if ($item->tanggal_tagihan) {
                                $bulan = \Carbon\Carbon::parse($item->tanggal_tagihan)->format('m');
                                $tagihanByBulan[$bulan][] = $item;
                            }
                        }
                    @endphp

                    <div class="row">
                        @foreach ($namaBulan as $kodeBulan => $namaBulan)
                            <div class="col-md-3">
                                @php
                                    $status = 'unpaid';
                                    $totalBulan = 0;
                                    $tagihanBulan = $tagihanByBulan[$kodeBulan] ?? []; // Hitung total dan cek status pembayaran
                                    foreach ($tagihanBulan as $item) {
                                        // Calculate total from tagihan_details
                                        foreach ($item->tagihan_details as $detail) {
                                            $totalBulan += $detail->jumlah_biaya;
                                            $totalDibayar = $detail->pembayaran
                                                ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                                ->sum('jumlah_dibayar');
                                            $sisaBayar = $detail->jumlah_biaya - $totalDibayar;

                                            if ($sisaBayar <= 0) {
                                                $status = 'paid';
                                            } elseif ($totalDibayar > 0 && $status != 'paid') {
                                                $status = 'partial';
                                            }
                                        }
                                    }
                                @endphp

                                <div class="month-box {{ $status }}">
                                    <div class="month-title">{{ $namaBulan }}</div>
                                    @if (count($tagihanBulan) > 0)
                                        <div class="month-amount">{{ formatRupiah($totalBulan) }}</div>
                                        @if ($status == 'paid')
                                            <div class="badge bg-success">LUNAS</div>
                                        @elseif($status == 'partial')
                                            <div class="badge bg-warning">ANGSUR</div>
                                        @else
                                            <div class="badge bg-danger">BELUM BAYAR</div>
                                        @endif
                                    @else
                                        <div class="text-muted small">Belum ada tagihan</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <h5 class="card-header">Daftar Tagihan Siswa</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="openBatchPaymentModal()">
                                <i class="bx bx-money"></i> Bayar Serentak
                            </button>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tagihan</th>
                                    <th>Periode</th>
                                    {{-- <th>Tanggal Tagihan</th>
                                    <th>Jatuh Tempo</th> --}}
                                    <th>Jumlah</th>
                                    <th>Sisa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse($tagihan as $item)
                                    @foreach ($item->tagihan_details as $detail)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>
                                                <strong>{{ $detail->nama_biaya }}</strong>
                                            </td>
                                            <td>
                                                @if ($item->tanggal_tagihan && $item->tanggal_jatuh_tempo)
                                                    @php
                                                        $bulan = \Carbon\Carbon::parse($item->tanggal_tagihan)->format(
                                                            'm',
                                                        );
                                                        $tahun = \Carbon\Carbon::parse($item->tanggal_tagihan)->format(
                                                            'Y',
                                                        );
                                                        $namaBulan = [
                                                            '01' => 'Jan',
                                                            '02' => 'Feb',
                                                            '03' => 'Mar',
                                                            '04' => 'Apr',
                                                            '05' => 'Mei',
                                                            '06' => 'Jun',
                                                            '07' => 'Jul',
                                                            '08' => 'Agu',
                                                            '09' => 'Sep',
                                                            '10' => 'Okt',
                                                            '11' => 'Nov',
                                                            '12' => 'Des',
                                                        ];
                                                    @endphp
                                                    {{ $namaBulan[$bulan] }} {{ $tahun }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            {{-- <td>{{ $item->tanggal_tagihan ? \Carbon\Carbon::parse($item->tanggal_tagihan)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td>{{ $item->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                            </td> --}}
                                            <td><strong>{{ formatRupiah($detail->jumlah_biaya) }}</strong></td>
                                            <td>
                                                @php
                                                    $totalBayar = $detail->pembayaran
                                                        ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                                        ->sum('jumlah_dibayar');
                                                    $sisaTagihan = $detail->jumlah_biaya - $totalBayar;
                                                @endphp
                                                <strong>{{ formatRupiah($sisaTagihan) }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    if ($sisaTagihan <= 0) {
                                                        $statusDisplay = 'lunas';
                                                    } elseif ($totalBayar > 0) {
                                                        $statusDisplay = 'angsur';
                                                    } else {
                                                        $statusDisplay = 'belum_lunas';
                                                    }
                                                @endphp
                                                @if ($statusDisplay == 'lunas')
                                                    <span class="badge bg-success">Lunas</span>
                                                @elseif($statusDisplay == 'angsur')
                                                    <span class="badge bg-warning">Angsur</span>
                                                @else
                                                    <span class="badge bg-danger">Belum Lunas</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route($routePrefix . '.show', $item->id) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="bx bx-show-alt"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-success btn-sm"
                                                        onclick="openPaymentModal('{{ $detail->id }}', '{{ $item->id }}')">
                                                        <i class="bx bx-money"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        onclick="openEditModal('{{ $detail->id }}')"
                                                        title="Edit Tagihan">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>
                                                    @php
                                                        $latest_payment = $detail->pembayaran
                                                            ->sortByDesc('id')
                                                            ->first();
                                                    @endphp
                                                    @if ($latest_payment)
                                                        <a href="{{ route('kwitansi.show', $latest_payment->id) }}"
                                                            target="blank" class="btn btn-primary btn-sm"
                                                            title="Cetak Kwitansi">
                                                            <i class="bx bx-printer"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('tagihan.destroyDetail', $detail->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus item tagihan ini?')"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="my-4">
                                                <i class="bx bx-file-find bx-lg text-muted"></i>
                                                <p class="text-muted mt-2">Belum ada tagihan untuk siswa ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @if ($tagihan->count() > 0)
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-end"><strong>Total Tagihan:</strong></td>
                                        <td>
                                            <strong>
                                                @php
                                                    $grandTotal = 0;
                                                    foreach ($tagihan as $item) {
                                                        $grandTotal += $item->tagihan_details->sum('jumlah_biaya');
                                                    }
                                                @endphp
                                                {{ formatRupiah($grandTotal) }}
                                            </strong>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-end"><strong>Total Sisa:</strong></td>
                                        <td>
                                            <strong>
                                                @php
                                                    $totalSisa = 0;
                                                    foreach ($tagihan as $item) {
                                                        foreach ($item->tagihan_details as $detail) {
                                                            $totalBayar = $detail->pembayaran->sum('jumlah_dibayar');
                                                            $totalSisa += $detail->jumlah_biaya - $totalBayar;
                                                        }
                                                    }
                                                @endphp
                                                {{ formatRupiah($totalSisa) }}
                                            </strong>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Form Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div id="payment-alert" class="alert" style="display: none;"></div>
                        <input type="hidden" name="tagihan_id" id="tagihan_id">
                        <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                        <input type="hidden" name="is_batch_payment" id="is_batch_payment" value="0">
                        <div id="detail_ids_container"></div>

                        <!-- Filter Periode untuk pembayaran serentak -->
                        <div id="batch_payment_filter" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Periode</label>
                                    <select class="form-select" id="batch_payment_month" onchange="filterBatchPayments()">
                                        <option value="">Semua Bulan</option>
                                        <option value="01">Januari</option>
                                        <option value="02">Februari</option>
                                        <option value="03">Maret</option>
                                        <option value="04">April</option>
                                        <option value="05">Mei</option>
                                        <option value="06">Juni</option>
                                        <option value="07">Juli</option>
                                        <option value="08">Agustus</option>
                                        <option value="09">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tahun</label>
                                    <select class="form-select" id="batch_payment_year" onchange="filterBatchPayments()">
                                        <option value="">Semua Tahun</option>
                                        @php
                                            $currentYear = date('Y');
                                            $startYear = $currentYear - 2;
                                            $endYear = $currentYear + 2;
                                        @endphp
                                        @for($year = $startYear; $year <= $endYear; $year++)
                                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Detail Tagihan</label>
                            <div id="tagihan_details_list" class="card">
                                <div class="card-body" id="single_payment_view">
                                    <h6 id="detail_nama_biaya" class="mb-1"></h6>
                                    <div class="text-muted small">
                                        Total Tagihan: <span id="detail_total_tagihan"></span><br>
                                        Sisa Tagihan: <span id="detail_sisa_tagihan"></span>
                                    </div>
                                </div>
                                <div class="card-body" id="batch_payment_view" style="display: none;">
                                    <div id="batch_items_list"></div>
                                    <div class="text-muted small mt-2">
                                        Total Tagihan: <span id="batch_total_tagihan"></span><br>
                                        Total Sisa: <span id="batch_sisa_tagihan"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Bank Sekolah (Opsional)</label>
                            <select name="bank_sekolah_id" class="form-select" id="bank_sekolah_id">
                                <option value="">Pilih Bank Sekolah (Opsional)</option>
                                @foreach (\App\Models\BankSekolah::all() as $bank)
                                    <option value="{{ $bank->id }}" data-nama="{{ $bank->nama_bank }}"
                                        data-rekening="{{ $bank->no_rekening }}"
                                        data-atas-nama="{{ $bank->atas_nama }}">
                                        {{ $bank->nama_bank }} - {{ $bank->no_rekening }} ({{ $bank->atas_nama }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="bank_info" style="display: none;">
                            <div class="alert alert-info">
                                <h6>Informasi Rekening:</h6>
                                <p class="mb-1"><strong>Bank:</strong> <span id="bank_nama"></span></p>
                                <p class="mb-1"><strong>No. Rekening:</strong> <span id="bank_rekening"></span></p>
                                <p class="mb-0"><strong>Atas Nama:</strong> <span id="bank_atas_nama"></span></p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah yang akan dibayar</label>
                            <input type="number" name="jumlah_dibayar" id="jumlah_dibayar" class="form-control"
                                required step="0.01" min="0" readonly>
                            <small class="text-muted">Total dari item yang dipilih: <span
                                    id="sisa_tagihan">0</span></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select" id="metode_pembayaran" required>
                                <option value="">Pilih Metode Pembayaran</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Tunai</option>
                            </select>
                        </div>

                        <div class="mb-3" id="bukti_bayar_field" style="display: none;">
                            <label class="form-label">Bukti Pembayaran</label>
                            <input type="file" name="bukti_bayar" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Upload bukti transfer (Gambar/PDF)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Pembayaran</label>
                            <input type="date" name="tanggal_bayar" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Konfirmasi</label>
                            <select name="status_konfirmasi" class="form-select" required>
                                <option value="Belum Dikonfirmasi">Belum Dikonfirmasi</option>
                                <option value="Sudah Dikonfirmasi">Sudah Dikonfirmasi</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="submitPayment">Simpan Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Detail Tagihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div id="edit-alert" class="alert" style="display: none;"></div>
                        <input type="hidden" name="detail_id" id="edit_detail_id">

                        <div class="mb-3">
                            <label class="form-label">Nama Biaya</label>
                            <input type="text" name="nama_biaya" id="edit_nama_biaya" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Biaya</label>
                            <input type="number" name="jumlah_biaya" id="edit_jumlah_biaya" class="form-control"
                                required min="0" step="1">
                            <small class="text-muted">Masukkan jumlah biaya tanpa tanda koma atau titik</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="submitEdit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize payment form handling
            initializePaymentForm();

            // Handle bank selection
            document.getElementById('bank_sekolah_id').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const bankInfo = document.getElementById('bank_info');

                if (this.value) {
                    document.getElementById('bank_nama').textContent = selectedOption.dataset.nama;
                    document.getElementById('bank_rekening').textContent = selectedOption.dataset.rekening;
                    document.getElementById('bank_atas_nama').textContent = selectedOption.dataset.atasNama;
                    bankInfo.style.display = 'block';
                } else {
                    bankInfo.style.display = 'none';
                }
            });

            // Handle payment method change
            document.getElementById('metode_pembayaran').addEventListener('change', function() {
                var buktiField = document.getElementById('bukti_bayar_field');
                var buktiInput = buktiField.querySelector('input[name="bukti_bayar"]');

                if (this.value === 'Bank Transfer') {
                    buktiField.style.display = 'block';
                    buktiInput.required = true;
                } else {
                    buktiField.style.display = 'none';
                    buktiInput.required = false;
                }
            });
        });

        function initializePaymentForm() {
            const form = document.getElementById('paymentForm');
            const alert = document.getElementById('payment-alert');
            const submitBtn = document.getElementById('submitPayment');
            const jumlahInput = document.getElementById('jumlah_dibayar');

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;

                let formData = new FormData(form);

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.disabled = false;

                        if (data.success) {
                            alert.className = 'alert alert-success';
                            alert.textContent = data.message;
                            alert.style.display = 'block';

                            // Auto close modal after 2 seconds
                            setTimeout(() => {
                                var modal = bootstrap.Modal.getInstance(document.getElementById(
                                    'paymentModal'));
                                modal.hide();
                                
                                // Check if this is a batch payment and show kwitansi
                                const isBatchPayment = document.getElementById('is_batch_payment').value === '1';
                                if (isBatchPayment && data.data && data.data.pembayaran_ids && data.data.pembayaran_ids.length > 0) {
                                    // Show batch kwitansi
                                    showBatchKwitansi(data.data.pembayaran_ids);
                                } else {
                                    // Reload page for single payment
                                    window.location.reload();
                                }
                            }, 2000);
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        submitBtn.disabled = false;
                        alert.className = 'alert alert-danger';
                        alert.textContent = error.message;
                        alert.style.display = 'block';
                    });
            });

            // Validate payment amount
            jumlahInput.addEventListener('input', function() {
                const maxAmount = parseFloat(this.max);
                const value = parseFloat(this.value);

                if (value > maxAmount) {
                    this.setCustomValidity(`Jumlah tidak boleh melebihi ${maxAmount}`);
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        function openBatchPaymentModal() {
            // Reset form
            document.getElementById('paymentForm').reset();
            document.getElementById('payment-alert').style.display = 'none';
            
            // Show modal
            var modalElement = document.getElementById('paymentModal');
            var modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Set batch payment flag and show filter
            document.getElementById('is_batch_payment').value = '1';
            document.getElementById('batch_payment_filter').style.display = 'block';
            document.getElementById('single_payment_view').style.display = 'none';
            document.getElementById('batch_payment_view').style.display = 'block';

            // Load all tagihan details initially
            loadBatchPaymentDetails();
        }

        function loadBatchPaymentDetails(month = '', year = '') {
            // Get all unpaid or partially paid tagihan details
            const rows = document.querySelectorAll('table tbody tr');
            let selectedDetails = [];
            let totalTagihan = 0;
            let totalSisa = 0;

            rows.forEach(row => {
                const statusBadge = row.querySelector('.badge');
                if (statusBadge && (statusBadge.textContent === 'Belum Lunas' || statusBadge.textContent === 'Angsur')) {
                    // Get periode from the row
                    const periodeText = row.querySelector('td:nth-child(3)').textContent.trim();
                    const [periodeBulan, periodeTahun] = periodeText.split(' ');
                    
                    // Skip if doesn't match filter
                    if (month && year) {
                        const bulanMap = {
                            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                            'Mei': '05', 'Jun': '06', 'Jul': '07', 'Agu': '08',
                            'Sep': '09', 'Okt': '10', 'Nov': '11', 'Des': '12'
                        };
                        
                        if (bulanMap[periodeBulan] !== month || periodeTahun !== year) {
                            return;
                        }
                    } else if (month) {
                        const bulanMap = {
                            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                            'Mei': '05', 'Jun': '06', 'Jul': '07', 'Agu': '08',
                            'Sep': '09', 'Okt': '10', 'Nov': '11', 'Des': '12'
                        };
                        if (bulanMap[periodeBulan] !== month) {
                            return;
                        }
                    } else if (year) {
                        if (periodeTahun !== year) {
                            return;
                        }
                    }

                    const paymentButton = row.querySelector('button[onclick*="openPaymentModal"]');
                    const matches = paymentButton.getAttribute('onclick').match(/openPaymentModal\('(\d+)',\s*'(\d+)'/);
                    const detailId = matches[1];
                    const tagihanId = matches[2];
                    
                    const namaBiaya = row.querySelector('td:nth-child(2)').textContent.trim();
                    const jumlahBiaya = row.querySelector('td:nth-child(6)').textContent.trim();
                    const sisaBiaya = row.querySelector('td:nth-child(7)').textContent.trim();

                    // Convert currency string to number
                    const sisaNumeric = parseFloat(sisaBiaya.replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.'));
                    
                    selectedDetails.push({
                        id: detailId,
                        tagihan_id: tagihanId,
                        nama: namaBiaya,
                        periode: periodeText,
                        jumlah: jumlahBiaya,
                        sisa: sisaBiaya,
                        sisaNumeric: sisaNumeric
                    });

                    totalSisa += sisaNumeric;
                }
            });

            // Update batch payment view
            const batchItemsList = document.getElementById('batch_items_list');
            const detailIdsContainer = document.getElementById('detail_ids_container');
            batchItemsList.innerHTML = '';
            detailIdsContainer.innerHTML = '';

            if (selectedDetails.length === 0) {
                batchItemsList.innerHTML = '<div class="alert alert-info">Tidak ada tagihan yang sesuai dengan filter</div>';
                document.getElementById('jumlah_dibayar').value = 0;
                document.getElementById('sisa_tagihan').textContent = formatRupiah(0);
                return;
            }

            // Group items by tagihan_id
            const groupedDetails = selectedDetails.reduce((acc, detail) => {
                if (!acc[detail.tagihan_id]) {
                    acc[detail.tagihan_id] = [];
                }
                acc[detail.tagihan_id].push(detail);
                return acc;
            }, {});

            // Create sections for each tagihan
            Object.entries(groupedDetails).forEach(([tagihanId, details]) => {
                const tagihanSection = document.createElement('div');
                tagihanSection.className = 'mb-3';
                
                // Add hidden input for tagihan_id
                const hiddenTagihanInput = document.createElement('input');
                hiddenTagihanInput.type = 'hidden';
                hiddenTagihanInput.name = 'tagihan_id';
                hiddenTagihanInput.value = tagihanId;
                detailIdsContainer.appendChild(hiddenTagihanInput);

                details.forEach(detail => {
                    // Add checkbox for each item
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'form-check mb-2';
                    itemDiv.innerHTML = `
                        <input class="form-check-input batch-item-checkbox" type="checkbox" 
                               id="detail_${detail.id}" value="${detail.id}" 
                               data-sisa="${detail.sisaNumeric}"
                               data-tagihan="${detail.tagihan_id}"
                               onchange="updateBatchTotal()">
                        <label class="form-check-label" for="detail_${detail.id}">
                            <strong>${detail.nama}</strong><br>
                            <small class="text-muted">
                                Periode: ${detail.periode}<br>
                                Sisa: ${detail.sisa}
                            </small>
                        </label>
                    `;
                    tagihanSection.appendChild(itemDiv);
                });

                batchItemsList.appendChild(tagihanSection);
            });

            document.getElementById('batch_total_tagihan').textContent = formatRupiah(totalSisa);
            document.getElementById('batch_sisa_tagihan').textContent = formatRupiah(totalSisa);
            
            // Set initial total to 0 since no checkboxes are checked yet
            document.getElementById('jumlah_dibayar').value = 0;
            document.getElementById('sisa_tagihan').textContent = formatRupiah(0);
        }

        function filterBatchPayments() {
            const month = document.getElementById('batch_payment_month').value;
            const year = document.getElementById('batch_payment_year').value;
            loadBatchPaymentDetails(month, year);
        }

        function updateBatchTotal() {
            const checkboxes = document.querySelectorAll('.batch-item-checkbox:checked');
            let total = 0;

            // Clear existing detail_ids
            const detailIdsContainer = document.getElementById('detail_ids_container');
            detailIdsContainer.innerHTML = '';

            checkboxes.forEach(checkbox => {
                // Add detail_id
                const hiddenDetailInput = document.createElement('input');
                hiddenDetailInput.type = 'hidden';
                hiddenDetailInput.name = 'detail_ids[]';
                hiddenDetailInput.value = checkbox.value;
                detailIdsContainer.appendChild(hiddenDetailInput);

                total += parseFloat(checkbox.dataset.sisa);
            });

            // Update the payment amount and display
            document.getElementById('jumlah_dibayar').value = total;
            document.getElementById('sisa_tagihan').textContent = formatRupiah(total);
        }

        function openPaymentModal(detailId, tagihanId) {
            // Reset form and show modal
            document.getElementById('paymentForm').reset();
            document.getElementById('payment-alert').style.display = 'none';
            
            var modalElement = document.getElementById('paymentModal');
            var modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Set single payment mode
            document.getElementById('is_batch_payment').value = '0';
            document.getElementById('single_payment_view').style.display = 'block';
            document.getElementById('batch_payment_view').style.display = 'none';
            document.getElementById('batch_payment_filter').style.display = 'none';

            // Set the tagihan_id and detail_id
            document.getElementById('tagihan_id').value = tagihanId;
            document.getElementById('detail_ids_container').innerHTML = `
                <input type="hidden" name="detail_ids[]" value="${detailId}">
            `;
            
            // Set default date
            document.querySelector('input[name="tanggal_bayar"]').value = new Date().toISOString().split('T')[0];

            // Fetch detail info
            fetch(`{{ url('/operator') }}/tagihan-detail/${detailId}/info`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) throw new Error(data.error);

                    // Update single payment view
                    document.getElementById('detail_nama_biaya').textContent = data.detail.nama_biaya;
                    document.getElementById('detail_total_tagihan').textContent = formatRupiah(data.total_tagihan);
                    document.getElementById('detail_sisa_tagihan').textContent = formatRupiah(data.remaining_amount);

                    // Set payment amount
                    document.getElementById('jumlah_dibayar').value = data.remaining_amount;
                    document.getElementById('sisa_tagihan').textContent = formatRupiah(data.remaining_amount);

                    // Update modal title
                    if (data.detail && data.detail.nama_siswa) {
                        document.querySelector('#paymentModalLabel').textContent = 
                            `Form Pembayaran - ${data.detail.nama_siswa}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    let alert = document.getElementById('payment-alert');
                    alert.className = 'alert alert-danger';
                    alert.textContent = 'Terjadi kesalahan saat mengambil data tagihan: ' + error.message;
                    alert.style.display = 'block';
                });
        }

        // Helper function to format currency
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Function to open edit modal
        function openEditModal(detailId) {
            // Fetch detail data
            fetch(`{{ url('/operator') }}/tagihan-detail/${detailId}/info`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Populate form
                    document.getElementById('edit_detail_id').value = detailId;
                    document.getElementById('edit_nama_biaya').value = data.detail.nama_biaya;
                    document.getElementById('edit_jumlah_biaya').value = data.total_tagihan;

                    // Update form action
                    document.getElementById('editForm').action =
                        `{{ url('/operator') }}/tagihan-detail/${detailId}/update`;

                    // Show modal
                    var modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data tagihan');
                });
        }

        // Initialize edit form handling
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitEdit');
            submitBtn.disabled = true;
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            fetch(this.action, {
                    method: 'PUT',
                    body: JSON.stringify(data),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    if (data.success) {
                        // Show success message
                        const alert = document.getElementById('edit-alert');
                        alert.className = 'alert alert-success';
                        alert.textContent = data.message;
                        alert.style.display = 'block';

                        // Reload page after 2 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    const alert = document.getElementById('edit-alert');
                    alert.className = 'alert alert-danger';
                    alert.textContent = error.message;
                    alert.style.display = 'block';
                });
        });

        // Function to show batch kwitansi
        function showBatchKwitansi(pembayaranIds) {
            // Create form to submit pembayaran IDs
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("kwitansi.showBatch") }}';
            form.target = '_blank';

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfToken);

            // Add pembayaran IDs
            pembayaranIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'pembayaran_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // Append form to body and submit
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            // Refresh halaman setelah form di-submit
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    </script>
@endpush

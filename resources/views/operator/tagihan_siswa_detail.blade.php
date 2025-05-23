@extends('layouts.app_sneat')

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
                                            if ($detail->status == 'lunas') {
                                                $status = 'paid';
                                            } elseif ($detail->status == 'angsur' && $status != 'paid') {
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
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tagihan</th>
                                    <th>Periode</th>
                                    <th>Tanggal Tagihan</th>
                                    <th>Jatuh Tempo</th>
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
                                            <td>{{ $item->tanggal_tagihan ? \Carbon\Carbon::parse($item->tanggal_tagihan)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td>{{ $item->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td><strong>{{ formatRupiah($detail->jumlah_biaya) }}</strong></td>
                                            <td>
                                                @php
                                                    $totalBayar = $detail->pembayaran->sum('jumlah_dibayar');
                                                    $sisaTagihan = $detail->jumlah_biaya - $totalBayar;
                                                @endphp
                                                <strong>{{ formatRupiah($sisaTagihan) }}</strong>
                                            </td>
                                            <td>
                                                @if ($detail->status == 'lunas')
                                                    <span class="badge bg-success">Lunas</span>
                                                @elseif($detail->status == 'angsur')
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
                                                    </a> <button type="button" class="btn btn-success btn-sm"
                                                        onclick="openPaymentModal('{{ $detail->id }}', '{{ $item->id }}')">
                                                        <i class="bx bx-money"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        onclick="openEditModal('{{ $detail->id }}')"
                                                        title="Edit Tagihan">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>
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
                <form id="paymentForm" action="{{ route('pembayaran.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div id="payment-alert" class="alert" style="display: none;"></div>
                        <input type="hidden" name="tagihan_id" id="tagihan_id">
                        <input type="hidden" name="detail_id" id="detail_id">
                        <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">

                        <div class="mb-3">
                            <label class="form-label">Jumlah yang akan dibayar</label>
                            <input type="number" name="jumlah_dibayar" id="jumlah_dibayar" class="form-control"
                                required step="0.01" min="0">
                            <small class="text-muted">Sisa yang harus dibayar: <span id="sisa_tagihan">0</span></small>
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
        });

        function initializePaymentForm() {
            const form = document.getElementById('paymentForm');
            const alert = document.getElementById('payment-alert');
            const submitBtn = document.getElementById('submitPayment');
            const jumlahInput = document.getElementById('jumlah_dibayar');

            // Show/hide bukti pembayaran field based on payment method
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

                            // Auto close modal after 2 seconds and reload page
                            setTimeout(() => {
                                var modal = bootstrap.Modal.getInstance(document.getElementById(
                                    'paymentModal'));
                                modal.hide();
                                window.location.reload();
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

        // Set tagihan_id and fetch tagihan details when modal is opened
        function openPaymentModal(detailId, tagihanId) {
            console.log('Opening modal for detail:', detailId, 'tagihan:', tagihanId);

            // Set the tagihan_id and detail_id in the form
            document.getElementById('tagihan_id').value = tagihanId;
            document.getElementById('detail_id').value = detailId;
            // First show the modal
            var modalElement = document.getElementById('paymentModal');
            var modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Then fetch the details
            fetch(`{{ url('/operator') }}/tagihan-detail/${detailId}/info`)
                .then(response => {
                    console.log('Response received:', response);
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    if (typeof data.remaining_amount === 'undefined') {
                        throw new Error('Data tagihan tidak valid');
                    }
                    // Set jumlah yang harus dibayar
                    let jumlahInput = document.querySelector('input[name="jumlah_dibayar"]');
                    let sisaTagihanText = document.getElementById('sisa_tagihan');

                    jumlahInput.value = data.remaining_amount;
                    jumlahInput.max = data.remaining_amount;
                    sisaTagihanText.textContent = formatRupiah(data.remaining_amount);

                    // Update form title with student details
                    if (data.detail && data.detail.nama_siswa) {
                        document.querySelector('#paymentModalLabel').textContent =
                            `Form Pembayaran - ${data.detail.nama_siswa}`;
                    } // Reset form and alert
                    document.getElementById('payment-alert').style.display = 'none';
                    document.getElementById('paymentForm').reset();

                    // Set values again
                    document.getElementById('tagihan_id').value = tagihanId;
                    document.getElementById('detail_id').value = detailId;
                    document.getElementById('jumlah_dibayar').value = data.remaining_amount;
                    document.getElementById('jumlah_dibayar').max = data.remaining_amount;
                    document.querySelector('input[name="tanggal_bayar"]').value = new Date().toISOString().split('T')[
                        0];
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
    </script>
@endpush

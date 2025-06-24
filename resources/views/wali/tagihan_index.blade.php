@extends('layouts.app_sneat_wali')

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
                    <div class="row mb-3">
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('wali.tagihan.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari tagihan (nama siswa, NISN, nama biaya)"
                                        value="{{ $search ?? '' }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    @if (isset($search) && $search != '')
                                        <a href="{{ route('wali.tagihan.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama Siswa</td>
                                    <td>NISN</td>
                                    <td>Total</td>
                                    <td>Status</td>
                                    {{-- <td>Jatuh Tempo</td> --}}
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @forelse ($tagihan as $item)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $item->siswa->nama ?? 'Data siswa tidak ditemukan' }}</td>
                                        <td>{{ $item->siswa->nisn ?? '-' }}</td>
                                        <td>{{ formatRupiah($item->tagihan_details->sum('jumlah_biaya')) }}</td>
                                        <td>
                                            @php
                                                $totalDetails = $item->tagihan_details->count();
                                                $lunasCount = 0;
                                                $angsurCount = 0;
                                                $belumLunasCount = 0;
                                                $baruCount = 0;

                                                foreach ($item->tagihan_details as $detail) {
                                                    // Hitung total pembayaran yang sudah dikonfirmasi
                                                    $totalDibayar = $detail
                                                        ->pembayaran()
                                                        ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                                        ->sum('jumlah_dibayar');
                                                    $sisaBayar = $detail->jumlah_biaya - $totalDibayar;

                                                    if ($sisaBayar <= 0) {
                                                        $lunasCount++;
                                                    } elseif ($totalDibayar > 0) {
                                                        $angsurCount++;
                                                    } elseif ($detail->status == 'baru') {
                                                        $baruCount++;
                                                    } else {
                                                        $belumLunasCount++;
                                                    }
                                                }

                                                if ($lunasCount == $totalDetails) {
                                                    $overallStatus = 'lunas';
                                                } elseif ($belumLunasCount > 0 || $baruCount > 0) {
                                                    $overallStatus = 'belum_lunas';
                                                } elseif ($angsurCount > 0) {
                                                    $overallStatus = 'angsur';
                                                } else {
                                                    $overallStatus = 'baru';
                                                }
                                            @endphp

                                            @if ($overallStatus == 'lunas')
                                                <span class="badge bg-label-success">Lunas</span>
                                            @elseif ($overallStatus == 'angsur')
                                                <span class="badge bg-label-info">Diangsur</span>
                                            @elseif ($overallStatus == 'belum_lunas')
                                                <span class="badge bg-label-warning">Belum Lunas</span>
                                            @else
                                                <span class="badge bg-label-secondary">Baru</span>
                                            @endif

                                            <br>
                                            <small class="text-muted">
                                                {{ $lunasCount }}/{{ $totalDetails }} lunas
                                            </small>
                                        </td>
                                        {{-- <td>{{ $item->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                        </td> --}}
                                        <td>
                                            <a href="{{ route('wali.tagihan.show', $item->id) }}"
                                                class="btn btn-sm btn-info"> <i class="fas fa-eye"></i> Detail</a>
                                            @if ($overallStatus != 'lunas')
                                                <button type="button" class="btn btn-sm btn-success"
                                                    onclick="openPaymentModal('{{ $item->id }}', '{{ $item->siswa->nama }}')">
                                                    <i class="fas fa-credit-card"></i> Bayar
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data tagihan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="card-footer">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Form Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" action="{{ route('wali.pembayaran.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div id="payment-alert" class="alert" style="display: none;"></div>

                        <!-- Info Alert -->
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Wali murid hanya dapat melakukan pembayaran melalui Bank Transfer.
                            Bukti pembayaran wajib diupload. Status konfirmasi akan diupdate oleh operator/admin setelah
                            verifikasi pembayaran.
                        </div>

                        <!-- Pembayaran Parsial Info -->
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Pembayaran Parsial:</strong> Anda dapat membayar sesuai kemampuan. Jika jumlah
                            pembayaran
                            kurang dari total tagihan yang dipilih, sistem akan membagi pembayaran secara proporsional
                            ke setiap item tagihan.
                        </div>

                        <input type="hidden" name="tagihan_id" id="tagihan_id">
                        <input type="hidden" name="siswa_id" id="siswa_id">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Siswa</label>
                                <input type="text" id="siswa_nama" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Tagihan</label>
                                <input type="text" id="total_tagihan" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Item yang akan dibayar</label>
                            <div id="tagihan_details_list">
                                <!-- Tagihan details will be loaded here -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Bank Sekolah</label>
                            <select name="bank_sekolah_id" class="form-select" id="bank_sekolah_id" required>
                                <option value="">Pilih Bank Sekolah</option>
                                @foreach (\App\Models\BankSekolah::all() as $bank)
                                    <option value="{{ $bank->id }}" data-nama="{{ $bank->nama_bank }}"
                                        data-rekening="{{ $bank->no_rekening }}" data-atas-nama="{{ $bank->atas_nama }}">
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
                            <div class="input-group">
                                <input type="number" name="jumlah_dibayar" id="jumlah_dibayar" class="form-control"
                                    required step="0.01" min="0" placeholder="Masukkan jumlah pembayaran">
                                <button type="button" class="btn btn-outline-secondary" id="btnMaxPayment"
                                    onclick="setMaxPayment()">
                                    <i class="fas fa-calculator"></i> Maksimal
                                </button>
                            </div>
                            <small class="text-muted">
                                Maksimal pembayaran: <span id="sisa_tagihan">Rp 0</span> |
                                <span id="payment_info">Silakan pilih item tagihan terlebih dahulu</span>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select" id="metode_pembayaran" required>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="mb-3" id="bukti_bayar_field">
                            <label class="form-label">Bukti Pembayaran</label>
                            <input type="file" name="bukti_bayar" class="form-control" accept="image/*,.pdf"
                                required>
                            <small class="text-muted">Upload bukti transfer (Gambar/PDF) - Wajib diisi</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Pembayaran</label>
                            <input type="date" name="tanggal_bayar" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Konfirmasi</label>
                            <input type="text" name="status_konfirmasi" class="form-control"
                                value="Belum Dikonfirmasi" readonly>
                            <small class="text-muted">Status konfirmasi akan diupdate oleh operator/admin setelah
                                verifikasi pembayaran</small>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize payment form handling
            initializePaymentForm();
        });

        function initializePaymentForm() {
            const form = document.getElementById('paymentForm');
            const alert = document.getElementById('payment-alert');
            const submitBtn = document.getElementById('submitPayment');
            const jumlahInput = document.getElementById('jumlah_dibayar');

            // Show/hide bukti pembayaran field based on payment method
            // Untuk wali, hanya Bank Transfer yang tersedia, jadi bukti pembayaran selalu wajib
            document.getElementById('metode_pembayaran').addEventListener('change', function() {
                // Tidak perlu menangani perubahan karena hanya ada Bank Transfer
                // Bukti pembayaran selalu wajib untuk wali
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
                const value = parseFloat(this.value) || 0;

                if (value > maxAmount) {
                    this.setCustomValidity(`Jumlah tidak boleh melebihi ${formatRupiah(maxAmount)}`);
                    this.classList.add('is-invalid');
                } else if (value < 0) {
                    this.setCustomValidity('Jumlah tidak boleh negatif');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }

                // Update informasi pembayaran
                const paymentInfo = document.getElementById('payment_info');
                if (value > 0) {
                    if (value === maxAmount) {
                        paymentInfo.textContent = `Pembayaran penuh untuk item yang dipilih`;
                        paymentInfo.className = 'text-success';
                    } else if (value < maxAmount) {
                        const sisa = maxAmount - value;
                        paymentInfo.textContent = `Sisa yang belum dibayar: ${formatRupiah(sisa)}`;
                        paymentInfo.className = 'text-warning';
                    }
                } else {
                    paymentInfo.textContent =
                        `Anda dapat membayar maksimal ${formatRupiah(maxAmount)} atau sesuai kemampuan`;
                    paymentInfo.className = 'text-muted';
                }
            });
        }

        function openPaymentModal(tagihanId, siswaNama) {
            console.log('=== OPENING PAYMENT MODAL ===');
            console.log('Tagihan ID:', tagihanId);
            console.log('Siswa Nama:', siswaNama);

            // Set the tagihan_id and siswa_nama in the form
            document.getElementById('tagihan_id').value = tagihanId;
            document.getElementById('siswa_nama').value = siswaNama;

            // Show the modal
            var modalElement = document.getElementById('paymentModal');
            var modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Simple approach - wait a bit then fetch data
            setTimeout(() => {
                console.log('=== FETCHING DATA ===');

                // Check if element exists
                const totalElement = document.getElementById('total_tagihan');
                console.log('Total tagihan element exists:', !!totalElement);
                if (totalElement) {
                    console.log('Current value:', totalElement.value);
                    console.log('Element attributes:', totalElement.attributes);
                }

                fetch(`{{ url('/walimurid') }}/tagihan/${tagihanId}/details`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(text || 'Network response was not ok');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('=== DATA RECEIVED ===');
                        console.log('Full data:', data);
                        console.log('Total tagihan from server:', data.total_tagihan);
                        console.log('Total tagihan type:', typeof data.total_tagihan);

                        if (data.error) {
                            throw new Error(data.error);
                        }

                        // Set total tagihan with multiple approaches
                        if (data.total_tagihan !== undefined && data.total_tagihan !== null) {
                            const formattedTotal = formatRupiah(data.total_tagihan);
                            console.log('Formatted total:', formattedTotal);

                            // Try multiple approaches to set the value
                            const element = document.getElementById('total_tagihan');
                            if (element) {
                                // Approach 1: Direct value assignment
                                element.value = formattedTotal;
                                console.log('Approach 1 - Element value after setting:', element.value);

                                // Approach 2: Using setAttribute
                                element.setAttribute('value', formattedTotal);
                                console.log('Approach 2 - Element value after setAttribute:', element.value);

                                // Approach 3: Trigger input event
                                element.dispatchEvent(new Event('input', {
                                    bubbles: true
                                }));
                                console.log('Approach 3 - Element value after event:', element.value);

                                // Approach 4: Using jQuery if available
                                if (typeof $ !== 'undefined') {
                                    $('#total_tagihan').val(formattedTotal);
                                    console.log('Approach 4 - jQuery value:', $('#total_tagihan').val());
                                }
                            } else {
                                console.error('Total tagihan element not found!');
                            }
                        } else {
                            console.warn('Total tagihan is undefined or null');
                            const element = document.getElementById('total_tagihan');
                            if (element) {
                                element.value = 'Rp 0';
                            }
                        }

                        // Set siswa_id for form submission
                        if (data.siswa && data.siswa.id) {
                            document.getElementById('siswa_id').value = data.siswa.id;
                        }

                        // Populate tagihan details
                        const detailsList = document.getElementById('tagihan_details_list');
                        detailsList.innerHTML = '';

                        if (data.details && data.details.length > 0) {
                            data.details.forEach(detail => {
                                if (detail.sisa_bayar > 0) {
                                    const detailDiv = document.createElement('div');
                                    detailDiv.className = 'card mb-2';
                                    detailDiv.innerHTML = `
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input detail-checkbox" type="checkbox" name="detail_ids[]" 
                                                       id="detail_${detail.id}" value="${detail.id}" 
                                                       data-sisa="${detail.sisa_bayar}" data-nama="${detail.nama_biaya}">
                                                <label class="form-check-label" for="detail_${detail.id}">
                                                    <strong>${detail.nama_biaya}</strong><br>
                                                    <small class="text-muted">
                                                        Total: ${formatRupiah(detail.jumlah_biaya)} | 
                                                        Sisa: ${formatRupiah(detail.sisa_bayar)}
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    `;
                                    detailsList.appendChild(detailDiv);
                                }
                            });
                        } else {
                            detailsList.innerHTML =
                                '<div class="alert alert-info">Tidak ada item tagihan yang dapat dibayar</div>';
                        }

                        // Handle checkbox selection
                        document.querySelectorAll('.detail-checkbox').forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                calculateTotal();
                            });
                        });

                        // Handle bank selection
                        document.getElementById('bank_sekolah_id').addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const bankInfo = document.getElementById('bank_info');

                            if (this.value) {
                                document.getElementById('bank_nama').textContent = selectedOption
                                    .dataset.nama;
                                document.getElementById('bank_rekening').textContent = selectedOption
                                    .dataset.rekening;
                                document.getElementById('bank_atas_nama').textContent = selectedOption
                                    .dataset.atasNama;
                                bankInfo.style.display = 'block';
                            } else {
                                bankInfo.style.display = 'none';
                            }
                        });

                        // Clear alert
                        document.getElementById('payment-alert').style.display = 'none';

                        // Set tanggal pembayaran
                        document.querySelector('input[name="tanggal_bayar"]').value = new Date().toISOString()
                            .split('T')[0];

                        // Final verification
                        setTimeout(() => {
                            const finalElement = document.getElementById('total_tagihan');
                            console.log('=== FINAL VERIFICATION ===');
                            console.log('Final element exists:', !!finalElement);
                            if (finalElement) {
                                console.log('Final value:', finalElement.value);
                                console.log('Final value type:', typeof finalElement.value);
                                console.log('Final getAttribute value:', finalElement.getAttribute(
                                    'value'));
                            }
                        }, 100);

                        // Initialize max payment button
                        document.getElementById('btnMaxPayment').disabled = true;
                    })
                    .catch(error => {
                        console.error('=== ERROR ===');
                        console.error('Error:', error);
                        let alert = document.getElementById('payment-alert');
                        alert.className = 'alert alert-danger';
                        alert.textContent = 'Terjadi kesalahan saat mengambil data tagihan: ' + error.message;
                        alert.style.display = 'block';
                    });
            }, 300);
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

        // Function to calculate total from selected checkboxes
        function calculateTotal() {
            const checkboxes = document.querySelectorAll('.detail-checkbox:checked');
            let total = 0;

            checkboxes.forEach(checkbox => {
                total += parseFloat(checkbox.dataset.sisa);
            });

            // Update informasi maksimal pembayaran
            document.getElementById('sisa_tagihan').textContent = formatRupiah(total);

            // Update informasi pembayaran
            const paymentInfo = document.getElementById('payment_info');
            if (total > 0) {
                paymentInfo.textContent = `Anda dapat membayar maksimal ${formatRupiah(total)} atau sesuai kemampuan`;
                document.getElementById('btnMaxPayment').disabled = false;
            } else {
                paymentInfo.textContent = 'Silakan pilih item tagihan terlebih dahulu';
                document.getElementById('btnMaxPayment').disabled = true;
            }

            // Set max attribute untuk validasi
            document.getElementById('jumlah_dibayar').max = total;
        }

        // Function to set maximum payment amount
        function setMaxPayment() {
            const maxAmount = parseFloat(document.getElementById('jumlah_dibayar').max);
            if (maxAmount > 0) {
                document.getElementById('jumlah_dibayar').value = maxAmount;
            }
        }
    </script>
@endpush

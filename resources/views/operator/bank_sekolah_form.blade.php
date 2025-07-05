@extends('layouts.app_sneat', ['title' => 'Bank Sekolah'])

@section('content')
    <style>
        /* Ensure modal backdrop is properly hidden */
        .modal-backdrop {
            z-index: 1040;
        }
        
        /* Force remove modal backdrop when needed */
        .modal-backdrop.force-remove {
            display: none !important;
            opacity: 0 !important;
        }
        
        /* Ensure body is not locked when modal is closed */
        body:not(.modal-open) {
            overflow: auto !important;
            padding-right: 0 !important;
        }

        /* Aggressive cleanup for modal backdrop */
        body:not(.modal-open) .modal-backdrop {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Additional cleanup for any remaining modal elements */
        .modal-backdrop:not(.show) {
            display: none !important;
        }

        /* Toast notification styles */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        }

        #toast-container .alert {
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 8px;
        }

        #toast-container .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        #toast-container .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        #toast-container .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        #toast-container .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        /* Toast animation */
        #toast-container .alert {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

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

                        <div class="form-group mb-3">
                            <label for="bank_id">Pilih Bank</label>
                            <div class="input-group">
                                <select name="bank_id" id="bank_id" class="form-control" required>
                                    <option value="">-- Pilih Bank --</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->id }}" data-kode="{{ $bank->sandi_bank }}"
                                            data-nama="{{ $bank->nama_bank }}"
                                            {{ old('bank_id', $selectedBankId ?? '') == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->nama_bank }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBankModal">
                                    <i class="menu-icon tf-icons bx bx-plus"></i> Tambah Bank Baru
                                </button>
                            </div>
                            <span class="text-danger">{{ $errors->first('bank_id') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="kode_bank">Kode Bank</label>
                            <input type="text" name="kode_bank" id="kode_bank" class="form-control"
                                value="{{ old('kode_bank', $model->kode_bank) }}" readonly>
                            <span class="text-danger">{{ $errors->first('kode_bank') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="nama_bank">Nama Bank</label>
                            <input type="text" name="nama_bank" id="nama_bank" class="form-control"
                                value="{{ old('nama_bank', $model->nama_bank) }}" readonly>
                            <span class="text-danger">{{ $errors->first('nama_bank') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="no_rekening">Nomor Rekening</label>
                            <input type="text" name="no_rekening" id="no_rekening" class="form-control"
                                value="{{ old('no_rekening', $model->no_rekening) }}">
                            <span class="text-danger">{{ $errors->first('no_rekening') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="atas_nama">Atas Nama</label>
                            <input type="text" name="atas_nama" id="atas_nama" class="form-control"
                                value="{{ old('atas_nama', $model->atas_nama) }}">
                            <span class="text-danger">{{ $errors->first('atas_nama') }}</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ old('keterangan', $model->keterangan) }}</textarea>
                            <span class="text-danger">{{ $errors->first('keterangan') }}</span>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ $button }}</button>
                            <a href="{{ route('bank-sekolah.index') }}" class="btn btn-secondary">Kembali</a>
                            <button type="button" class="btn btn-warning" onclick="cleanupModal()" style="display: none;" id="cleanupBtn">
                                <i class="menu-icon tf-icons bx bx-refresh"></i> Bersihkan Modal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Bank Baru -->
    <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBankModalLabel">Tambah Bank Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addBankForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small>
                                <strong>Tips:</strong> Pastikan kode bank belum ada dalam daftar. 
                                Beberapa kode bank populer: 014 (BCA), 008 (Mandiri), 009 (BNI), 002 (BRI)
                            </small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="sandi_bank_modal">Kode Bank</label>
                            <input type="text" name="sandi_bank" id="sandi_bank_modal" class="form-control" required>
                            <small class="form-text text-muted">Contoh: 014 (BCA), 008 (Mandiri), 009 (BNI)</small>
                            <div id="bank-suggestion" class="mt-2" style="display: none;">
                                <small class="text-warning">
                                    <i class="menu-icon tf-icons bx bx-info-circle"></i>
                                    <span id="suggestion-text"></span>
                                </small>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="nama_bank_modal">Nama Bank</label>
                            <input type="text" name="nama_bank" id="nama_bank_modal" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Bank</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global function to clean up modal
        function cleanupModal() {
            // Remove all modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Remove modal-open class and reset body styles
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Hide cleanup button
            const cleanupBtn = document.getElementById('cleanupBtn');
            if (cleanupBtn) {
                cleanupBtn.style.display = 'none';
            }

            // Force cleanup with CSS
            const style = document.createElement('style');
            style.textContent = `
                .modal-backdrop {
                    display: none !important;
                    opacity: 0 !important;
                    visibility: hidden !important;
                    pointer-events: none !important;
                }
            `;
            document.head.appendChild(style);

            // Remove the style after 1 second
            setTimeout(() => {
                if (style.parentNode) {
                    style.remove();
                }
            }, 1000);
        }

        // Function to show cleanup button if modal backdrop exists
        function checkAndShowCleanupButton() {
            const backdrop = document.querySelector('.modal-backdrop');
            const cleanupBtn = document.getElementById('cleanupBtn');
            if (backdrop && cleanupBtn) {
                cleanupBtn.style.display = 'inline-block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const bankSelect = document.getElementById('bank_id');
            const kodeBankInput = document.getElementById('kode_bank');
            const namaBankInput = document.getElementById('nama_bank');

            // Function to show toast notification
            function showToast(message, type = 'success') {
                // Create toast container if it doesn't exist
                let toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                        max-width: 350px;
                    `;
                    document.body.appendChild(toastContainer);
                }

                // Create toast element
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} alert-dismissible fade show`;
                toast.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                toastContainer.appendChild(toast);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 3000);
            }

            bankSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    kodeBankInput.value = selectedOption.getAttribute('data-kode');
                    namaBankInput.value = selectedOption.getAttribute('data-nama');
                } else {
                    kodeBankInput.value = '';
                    namaBankInput.value = '';
                }
            });

            // Trigger change event on page load if there's a selected value
            if (bankSelect.value) {
                bankSelect.dispatchEvent(new Event('change'));
            }

            // Handle form submission for adding new bank
            document.getElementById('addBankForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const sandiBank = formData.get('sandi_bank');
                const namaBank = formData.get('nama_bank');
                
                // Client-side validation
                if (!sandiBank || !namaBank) {
                    showToast('Kode bank dan nama bank harus diisi', 'warning');
                    return;
                }
                
                // Check if bank code already exists in dropdown
                const existingBank = Array.from(bankSelect.options).find(option => 
                    option.getAttribute('data-kode') === sandiBank
                );
                
                if (existingBank) {
                    showToast(`Kode bank ${sandiBank} sudah ada (${existingBank.text}). Silakan pilih bank yang sudah ada atau gunakan kode bank yang berbeda.`, 'warning');
                    return;
                }
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';

                // Prepare request data
                const requestData = {
                    sandi_bank: sandiBank,
                    nama_bank: namaBank
                };

                console.log('Sending request:', requestData);

                fetch('{{ route("bank.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        // Handle validation errors (422) and other errors
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        // Add new bank to select options
                        const newOption = new Option(data.data.nama_bank, data.data.id);
                        newOption.setAttribute('data-kode', data.data.sandi_bank);
                        newOption.setAttribute('data-nama', data.data.nama_bank);
                        bankSelect.add(newOption);
                        
                        // Select the newly added bank
                        bankSelect.value = data.data.id;
                        bankSelect.dispatchEvent(new Event('change'));
                        
                        // Reset form
                        document.getElementById('addBankForm').reset();
                        
                        // Close modal
                        const modal = document.getElementById('addBankModal');
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        
                        // Cleanup and show success message
                        setTimeout(() => {
                            cleanupModal();
                            showToast('Bank berhasil ditambahkan!', 'success');
                        }, 300);
                    } else {
                        showToast('Gagal menambahkan bank: ' + (data.message || 'Terjadi kesalahan'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error details:', error);
                    console.error('Error message:', error.message);
                    console.error('Error stack:', error.stack);
                    
                    let errorMessage = 'Terjadi kesalahan saat menambahkan bank';
                    
                    if (error.message.includes('Kode bank sudah ada')) {
                        errorMessage = 'Kode bank sudah ada dalam database. Silakan pilih bank yang sudah ada atau gunakan kode bank yang berbeda.';
                        showToast(errorMessage, 'warning');
                    } else if (error.message.includes('Validasi gagal')) {
                        errorMessage = error.message;
                        showToast(errorMessage, 'warning');
                    } else if (error.message.includes('HTTP error')) {
                        errorMessage = 'Server error: ' + error.message;
                        showToast(errorMessage, 'danger');
                    } else if (error.message.includes('JSON')) {
                        errorMessage = 'Invalid response from server';
                        showToast(errorMessage, 'danger');
                    } else if (error.message.includes('Network')) {
                        errorMessage = 'Koneksi jaringan bermasalah';
                        showToast(errorMessage, 'danger');
                    } else {
                        showToast(errorMessage, 'danger');
                    }
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });

            // Handle modal close events
            const modal = document.getElementById('addBankModal');
            
            modal.addEventListener('hidden.bs.modal', function() {
                setTimeout(cleanupModal, 100);
            });

            // Handle close button clicks
            const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(cleanupModal, 200);
                });
            });

            // Handle backdrop clicks
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    setTimeout(cleanupModal, 200);
                }
            });

            // Check for modal backdrop every 2 seconds and show cleanup button if needed
            setInterval(checkAndShowCleanupButton, 2000);

            // Add bank code suggestion functionality
            const sandiBankInput = document.getElementById('sandi_bank_modal');
            const bankSuggestion = document.getElementById('bank-suggestion');
            const suggestionText = document.getElementById('suggestion-text');

            if (sandiBankInput) {
                sandiBankInput.addEventListener('input', function() {
                    const inputValue = this.value.trim();
                    
                    if (inputValue.length >= 2) {
                        // Check if bank code already exists
                        const existingBank = Array.from(bankSelect.options).find(option => 
                            option.getAttribute('data-kode') === inputValue
                        );
                        
                        if (existingBank) {
                            suggestionText.textContent = `Kode ${inputValue} sudah ada: ${existingBank.text}. Silakan pilih bank yang sudah ada atau gunakan kode yang berbeda.`;
                            bankSuggestion.style.display = 'block';
                        } else {
                            bankSuggestion.style.display = 'none';
                        }
                    } else {
                        bankSuggestion.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection

@extends('layouts.app_sneat', ['title' => 'Tagihan'])

@section('styles')
{{-- Tambahkan Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('js')
{{-- Tambahkan Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- Tambahkan Moment.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    $(document).ready(function() {
        // Fungsi untuk mendapatkan tanggal default
        function getDefaultDates() {
            const today = new Date();
            return {
                tagihan: today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01',
                jatuhTempo: today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-28'
            };
        }

        // Set tanggal default saat pertama kali load
        const defaultDates = getDefaultDates();
        if (!$('input[name="tanggal_tagihan"]').val()) {
            $('input[name="tanggal_tagihan"]').val(defaultDates.tagihan);
        }
        if (!$('input[name="tanggal_jatuh_tempo"]').val()) {
            $('input[name="tanggal_jatuh_tempo"]').val(defaultDates.jatuhTempo);
        }

        $('#form-tagihan-ajax').submit(function(e) {
            e.preventDefault();
            
            // Tampilkan loading overlay
            showLoading();
            
            // Disable submit button and show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
            
            // Clear previous alerts and validation errors
            $('.alert').remove();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            
            $.ajax({
                type: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    // Show success message
                    const alertSuccess = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.message || 'Tagihan berhasil dibuat!'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#form-tagihan-ajax').before(alertSuccess);
                    
                    // Reset form if creating new tagihan
                    if (!$('input[name="_method"]').val()) {
                        $('#form-tagihan-ajax')[0].reset();
                        
                        // Reset tanggal ke default
                        const defaultDates = getDefaultDates();
                        $('input[name="tanggal_tagihan"]').val(defaultDates.tagihan);
                        $('input[name="tanggal_jatuh_tempo"]').val(defaultDates.jatuhTempo);
                        
                        // Reset checkboxes
                        $('input[type="checkbox"]').prop('checked', false);
                        // Reset total
                        $('#total-tagihan').text('Rp 0');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan! Silakan coba lagi.';
                    
                    // Handle validation errors
                    if (xhr.status === 422) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            errorMessage = '<ul class="mb-0">';
                            
                            Object.keys(response.errors).forEach(function(key) {
                                // Remove validation error styling
                                $(`[name="${key}"]`).removeClass('is-invalid');
                                $(`[name="${key}[]"]`).removeClass('is-invalid');
                                
                                // Add new validation error styling and messages
                                response.errors[key].forEach(function(message) {
                                    errorMessage += `<li>${message}</li>`;
                                    
                                    // Handle array inputs (like checkboxes)
                                    if (key.includes('[]')) {
                                        const baseKey = key.replace('[]', '');
                                        $(`[name="${baseKey}[]"]`).addClass('is-invalid');
                                        // Add error message after the checkbox container
                                        if (!$(`.invalid-feedback[data-key="${baseKey}"]`).length) {
                                            $('.row.mb-3').first().append(`
                                                <div class="invalid-feedback d-block" data-key="${baseKey}">${message}</div>
                                            `);
                                        }
                                    } else {
                                        $(`[name="${key}"]`).addClass('is-invalid');
                                        // Add error message after the input
                                        if (!$(`[name="${key}"]`).next('.invalid-feedback').length) {
                                            $(`[name="${key}"]`).after(`
                                                <div class="invalid-feedback">${message}</div>
                                            `);
                                        }
                                    }
                                });
                            });
                            
                            errorMessage += '</ul>';
                        } else if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // Show error message
                    const alertError = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#form-tagihan-ajax').before(alertError);
                },
                complete: function() {
                    // Sembunyikan loading overlay
                    hideLoading();
                    
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).text(originalText);
                    
                    // Scroll to top if there are messages
                    if ($('.alert').length) {
                        $('html, body').animate({
                            scrollTop: $('.alert').first().offset().top - 100
                        }, 500);
                    }
                }
            });
        });
        
        // Hitung total tagihan ketika checkbox berubah
        $('input[name="biaya_id[]"]').change(function() {
            let total = 0;
            $('input[name="biaya_id[]"]:checked').each(function() {
                total += parseInt($(this).data('jumlah')) || 0;
            });
            $('#total-tagihan').text(formatRupiah(total));
        });
        
        // Toggle mode pemilihan siswa
        $('input[name="mode_siswa"]').change(function() {
            const mode = $(this).val();
            
            if (mode === 'single') {
                $('#single-student-section').show();
                $('#filter-siswa-section').hide();
                // Disable filter inputs
                $('#filter-siswa-section select').prop('disabled', true);
            } else {
                $('#single-student-section').hide();
                $('#filter-siswa-section').show();
                // Enable filter inputs
                $('#filter-siswa-section select').prop('disabled', false);
            }
        });
        
        // Tampilkan informasi siswa yang dipilih
        $('#siswa-select').on('select2:select', function(e) {
            const data = e.params.data;
            const siswaInfo = $('#siswa-info');
            const siswaDetails = $('#siswa-details');
            
            if (data && data.id) {
                siswaDetails.html(`
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td><strong>Nama:</strong></td><td>${data.nama}</td></tr>
                        <tr><td><strong>NISN:</strong></td><td>${data.nisn}</td></tr>
                        <tr><td><strong>NIS:</strong></td><td>${data.nis}</td></tr>
                        <tr><td><strong>Kelas:</strong></td><td>${data.kelas}</td></tr>
                        <tr><td><strong>Jurusan:</strong></td><td>${data.jurusan}</td></tr>
                        <tr><td><strong>Angkatan:</strong></td><td>${data.angkatan}</td></tr>
                        <tr><td><strong>Jenis Kelamin:</strong></td><td>${data.jenis_kelamin}</td></tr>
                    </table>
                `);
                siswaInfo.show();
            } else {
                siswaInfo.hide();
            }
        });
        
        // Sembunyikan info siswa ketika dropdown dikosongkan
        $('#siswa-select').on('select2:clear', function(e) {
            $('#siswa-info').hide();
        });
        
        // Inisialisasi Select2 untuk dropdown siswa
        $('#siswa-select').select2({
            placeholder: 'Cari siswa berdasarkan nama atau NISN...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            language: 'id',
            ajax: {
                url: '{{ route("siswa.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            templateResult: formatSiswaOption,
            templateSelection: formatSiswaSelection,
            minimumInputLength: 2
        });
        
        // Format untuk dropdown option
        function formatSiswaOption(siswa) {
            if (siswa.loading) return siswa.text;
            if (!siswa.id) return siswa.text;
            
            return $(`
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${siswa.nama}</strong><br>
                        <small class="text-muted">NISN: ${siswa.nisn}</small>
                    </div>
                    <div class="text-end">
                        <small class="badge bg-primary">${siswa.kelas}</small><br>
                        <small class="text-muted">${siswa.jurusan}</small>
                    </div>
                </div>
            `);
        }
        
        // Format untuk selected option
        function formatSiswaSelection(siswa) {
            if (!siswa.id) return siswa.text;
            return `${siswa.nama} - ${siswa.nisn} (${siswa.kelas})`;
        }
        
        // Format number to Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }
    });
</script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            {{-- Alert/Error --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    </div>
            @endif

                    <form action="{{ isset($model->id) ? route($route, $model->id) : route($route) }}" id="form-tagihan-ajax" method="POST">
                        @csrf
                        @if (isset($model->id))
                            @method('PUT')
                        @endif

                {{-- Card: Pilih Biaya --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">1. Pilih Biaya</h6>
                    </div>
                    <div class="card-body">
                                <div class="row">
                                    @foreach ($biaya as $item)
                                        <div class="col-md-6 mb-3">
                                    <div class="form-check mb-2">
                                                        <input type="checkbox" name="biaya_id[]" value="{{ $item->id }}"
                                                            class="form-check-input @error('biaya_id') is-invalid @enderror"
                                                            id="biaya_{{ $item->id }}" data-jumlah="{{ $item->total_tagihan }}"
                                                            {{ is_array(old('biaya_id')) && in_array($item->id, old('biaya_id')) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="biaya_{{ $item->id }}">
                                                            <strong>{{ $item->nama }}</strong>
                                            <span class="text-primary ms-2">{{ formatRupiah($item->total_tagihan) }}</span>
                                                        </label>
                                                    </div>
                                                    @if($item->children->count() > 0)
                                        <ul class="list-unstyled ms-4 mb-0">
                                                                @foreach($item->children as $child)
                                                <li><small>• {{ $child->nama }}: {{ formatRupiah($child->jumlah) }}</small></li>
                                                                @endforeach
                                                            </ul>
                                                    @endif
                                        </div>
                                    @endforeach
                                </div>
                                @error('biaya_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                {{-- Card: Mode Pemilihan Siswa --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">2. Pilih Siswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode_siswa" id="mode_filter" value="filter" checked>
                                <label class="form-check-label" for="mode_filter">
                                    <i class="bx bx-filter-alt"></i> Filter Siswa (Bulk)
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode_siswa" id="mode_single" value="single">
                                <label class="form-check-label" for="mode_single">
                                    <i class="bx bx-user"></i> Pilih Siswa Spesifik
                                </label>
                            </div>
                        </div>
                        {{-- Single Student Selection --}}
                        <div class="row mb-3" id="single-student-section" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label">Cari Siswa</label>
                                <select name="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror" id="siswa-select">
                                    <option value="">Cari siswa berdasarkan nama atau NISN...</option>
                                </select>
                                @error('siswa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="siswa-info" class="alert alert-info mt-2" style="display: none;">
                                    <h6>Informasi Siswa:</h6>
                                    <div id="siswa-details"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Filter Siswa (Bulk) --}}
                        <div class="row mb-3" id="filter-siswa-section">
                            <div class="col-md-3">
                                <label class="form-label">Angkatan <small class="text-muted">(opsional)</small></label>
                                <select name="angkatan" class="form-control @error('angkatan') is-invalid @enderror">
                                    <option value="">Pilih Angkatan</option>
                                    @foreach ($angkatan as $item)
                                        <option value="{{ $item }}" {{ old('angkatan') == $item ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                                @error('angkatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jurusan <small class="text-muted">(opsional)</small></label>
                                <select name="jurusan" class="form-control @error('jurusan') is-invalid @enderror">
                                    <option value="">Semua Jurusan</option>
                                    @foreach ($jurusan as $kode => $nama)
                                        <option value="{{ $kode }}" {{ old('jurusan') == $kode ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                                @error('jurusan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kelas <small class="text-muted">(opsional)</small></label>
                                <select name="kelas" class="form-control @error('kelas') is-invalid @enderror">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelas as $item)
                                        <option value="{{ $item }}" {{ old('kelas') == $item ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                                @error('kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jenis Kelamin <small class="text-muted">(opsional)</small></label>
                                <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="">Semua Jenis Kelamin</option>
                                    <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                            </div>
                        </div>

                {{-- Card: Tahun Pelajaran & Tanggal --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">3. Tahun Pelajaran & Tanggal</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tahun Pelajaran</label>
                                <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control">
                                    @foreach($tahunPelajarans as $tp)
                                        <option value="{{ $tp->id }}" {{ (old('tahun_pelajaran_id', $tahunAktif?->id) == $tp->id) ? 'selected' : '' }}>{{ $tp->nama }}{{ $tp->is_aktif ? ' (Aktif)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Tagihan</label>
                                <input type="date" name="tanggal_tagihan" class="form-control @error('tanggal_tagihan') is-invalid @enderror" value="{{ date('Y-m-01') }}">
                                @error('tanggal_tagihan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tanggal_jatuh_tempo" class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" value="{{ date('Y-m-28') }}">
                                @error('tanggal_jatuh_tempo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            @php
                                // Ambil tahun pelajaran aktif, fallback ke string default jika tidak ada
                                $tpNama = $tahunAktif?->nama ?? '2025/2026';
                                // Ekstrak tahun awal dan akhir
                                preg_match('/(\d{4})[\/\-](\d{4})/', $tpNama, $matches);
                                $tahunAwal = $matches[1] ?? '2025';
                                $tahunAkhir = $matches[2] ?? '2026';
                            @endphp
                            <input class="form-check-input" type="checkbox" name="generate_1_tahun" id="generate_1_tahun" value="1" {{ old('generate_1_tahun') ? 'checked' : '' }}>
                            <label class="form-check-label" for="generate_1_tahun">
                                Generate Tagihan 1 Tahun (Juli {{ $tahunAwal }} - Juni {{ $tahunAkhir }})
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Card: Keterangan --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">4. Keterangan (Opsional)</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary px-4 py-2">Simpan Tagihan</button>
                </div>
            </form>
            </div>
        </div>
    </div>
@endsection

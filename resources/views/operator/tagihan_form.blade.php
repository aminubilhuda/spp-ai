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
        // --- CACHE SELECTORS ---
        const $form = $('#form-tagihan-ajax');
        const $modeSiswaRadios = $('input[name="mode_siswa"]');
        const $spesifikSiswaSection = $('#spesifik-siswa-section');
        const $filterSiswaSection = $('#filter-siswa-section');
        const $spesifikSiswaSelect = $('#siswa-select-spesifik');
        const $filterSelects = $filterSiswaSection.find('select.select2');
        const $filterJenisKelamin = $filterSiswaSection.find('select[name="jenis_kelamin"]');

        // --- INITIALIZE SELECT2 ---
        $filterSelects.each(function() {
            $(this).select2({
                placeholder: $(this).data('placeholder') || 'Pilih...',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                language: 'id'
            });
        });

        $spesifikSiswaSelect.select2({
            placeholder: 'Cari & pilih satu atau beberapa siswa',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            multiple: true,
            language: 'id',
            ajax: {
                url: '{{ route("siswa.search") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { search: params.term, page: params.page };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return { results: data.results, pagination: data.pagination };
                },
                cache: true
            },
            templateResult: formatSiswaOption,
            templateSelection: formatSiswaSelection,
            minimumInputLength: 2
        });

        // --- MAIN LOGIC: MODE SWITCHING ---
        function handleModeChange() {
            const selectedMode = $modeSiswaRadios.filter(':checked').val();

            if (selectedMode === 'spesifik') {
                $spesifikSiswaSection.show();
                $filterSiswaSection.hide();
                $spesifikSiswaSelect.prop('disabled', false);
                $filterSelects.prop('disabled', true);
                $filterJenisKelamin.prop('disabled', true);
                $filterSelects.val(null).trigger('change');
                $filterJenisKelamin.val('');

            } else { // mode === 'filter'
                $filterSiswaSection.show();
                $spesifikSiswaSection.hide();
                $filterSelects.prop('disabled', false);
                $filterJenisKelamin.prop('disabled', false);
                $spesifikSiswaSelect.prop('disabled', true);
                $spesifikSiswaSelect.val(null).trigger('change');
            }
        }

        $modeSiswaRadios.on('change', handleModeChange);

        // --- HELPERS & FORM SUBMISSION ---
        function formatSiswaOption(siswa) {
            if (siswa.loading) return siswa.text;
            if (!siswa.id) return siswa.text;
            const jurusan = siswa.jurusan || 'N/A';
            return $(`
                <div class="d-flex justify-content-between align-items-center">
                    <div><strong>${siswa.nama}</strong><br><small class="text-muted">NISN: ${siswa.nisn}</small></div>
                    <div class="text-end"><small class="badge bg-primary">${siswa.kelas}</small><br><small class="text-muted">${jurusan}</small></div>
                </div>`);
        }

        function formatSiswaSelection(siswa) {
            return siswa.nama || siswa.text;
        }

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(number);
        }

        function getDefaultDates() {
            const today = new Date();
            return {
                tagihan: today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01',
                jatuhTempo: today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-28'
            };
        }

        const defaultDates = getDefaultDates();
        if (!$('input[name="tanggal_tagihan"]').val()) {
            $('input[name="tanggal_tagihan"]').val(defaultDates.tagihan);
        }
        if (!$('input[name="tanggal_jatuh_tempo"]').val()) {
            $('input[name="tanggal_jatuh_tempo"]').val(defaultDates.jatuhTempo);
        }

        $('input[name="biaya_id[]"]').change(function() {
            let total = 0;
            $('input[name="biaya_id[]"]:checked').each(function() {
                total += parseInt($(this).data('jumlah')) || 0;
            });
            $('#total-tagihan').text(formatRupiah(total));
        });

        $form.submit(function(e) {
            e.preventDefault();
            showLoading();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
            $('.alert, .invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');

            $.ajax({
                type: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    const alertSuccess = `<div class="alert alert-success alert-dismissible fade show" role="alert">${response.message || 'Tagihan berhasil dibuat!'}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $form.before(alertSuccess);
                    if (!$('input[name="_method"]').val()) {
                        $form[0].reset();
                        const defaultDates = getDefaultDates();
                        $('input[name="tanggal_tagihan"]').val(defaultDates.tagihan);
                        $('input[name="tanggal_jatuh_tempo"]').val(defaultDates.jatuhTempo);
                        $('.select2').val(null).trigger('change');
                        handleModeChange();
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan! Silakan coba lagi.';
                    if (xhr.status === 422) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            errorMessage = '<ul class="mb-0">';
                            Object.keys(response.errors).forEach(function(key) {
                                const fieldName = key.replace(/\./g, '_');
                                const input = $(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                                input.addClass('is-invalid');
                                response.errors[key].forEach(function(message) {
                                    errorMessage += `<li>${message}</li>`;
                                    if (input.next('.invalid-feedback').length === 0) {
                                        input.after(`<div class="invalid-feedback">${message}</div>`);
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
                    const alertError = `<div class="alert alert-danger alert-dismissible fade show" role="alert">${errorMessage}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $form.before(alertError);
                },
                complete: function() {
                    hideLoading();
                    submitBtn.prop('disabled', false).text(originalText);
                    if ($('.alert').length) {
                        $('html, body').animate({ scrollTop: $('.alert').first().offset().top - 100 }, 500);
                    }
                }
            });
        });

        // --- INITIALIZE PAGE ---
        handleModeChange();
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
                        <h6 class="mb-0">2. Pilih Penerima Tagihan</h6>
                    </div>
                    <div class="card-body">
                        <div class="btn-group w-100 mb-4" role="group" aria-label="Pilih mode">
                            <input type="radio" class="btn-check" name="mode_siswa" id="mode_filter" value="filter" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="mode_filter"><i class="bx bx-filter-alt me-1"></i>Filter Massal</label>

                            <input type="radio" class="btn-check" name="mode_siswa" id="mode_spesifik" value="spesifik" autocomplete="off">
                            <label class="btn btn-outline-primary" for="mode_spesifik"><i class="bx bx-user me-1"></i>Pilih Siswa Spesifik</label>
                        </div>

                        {{-- Filter Siswa (Bulk) --}}
                        <div id="filter-siswa-section">
                            <p class="text-muted small mb-3">Gunakan filter untuk membuat tagihan untuk sekelompok siswa sekaligus. Kosongkan filter untuk memilih semua siswa.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Angkatan</label>
                                    <select name="angkatan[]" class="form-control select2" multiple data-placeholder="Semua Angkatan">
                                        @foreach ($angkatan as $item)
                                            <option value="{{ $item }}" {{ (collect(old('angkatan'))->contains($item)) ? 'selected' : '' }}>{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jurusan</label>
                                    <select name="jurusan[]" class="form-control select2" multiple data-placeholder="Semua Jurusan">
                                        @foreach ($jurusan as $kode => $nama)
                                            <option value="{{ $kode }}" {{ (collect(old('jurusan'))->contains($kode)) ? 'selected' : '' }}>{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas[]" class="form-control select2" multiple data-placeholder="Semua Kelas">
                                        @foreach ($kelas as $item)
                                            <option value="{{ $item }}" {{ (collect(old('kelas'))->contains($item)) ? 'selected' : '' }}>{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-control">
                                        <option value="">Semua</option>
                                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Spesifik Siswa Selection --}}
                        <div id="spesifik-siswa-section" style="display: none;">
                            <p class="text-muted small mb-3">Cari dan pilih satu atau beberapa siswa secara spesifik.</p>
                             <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">Nama Siswa</label>
                                    <select name="siswa_id[]" id="siswa-select-spesifik" class="form-control select2" multiple>
                                    </select>
                                    @error('siswa_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
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

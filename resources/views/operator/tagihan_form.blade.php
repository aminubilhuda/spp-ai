@extends('layouts.app_sneat', ['title' => 'Tagihan'])

@section('js')
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
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    {{-- Tambahkan div untuk menampilkan total tagihan --}}
                    <div class="alert alert-info mb-4">
                        <strong>Total Tagihan:</strong> <span id="total-tagihan">Rp 0</span>
                    </div>

                    <form action="{{ isset($model->id) ? route($route, $model->id) : route($route) }}" id="form-tagihan-ajax" method="POST">
                        @csrf
                        @if (isset($model->id))
                            @method('PUT')
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Pilih Biaya</label>
                                <div class="row">
                                    @foreach ($biaya as $item)
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="biaya_id[]" value="{{ $item->id }}"
                                                            class="form-check-input @error('biaya_id') is-invalid @enderror"
                                                            id="biaya_{{ $item->id }}" data-jumlah="{{ $item->total_tagihan }}"
                                                            {{ is_array(old('biaya_id')) && in_array($item->id, old('biaya_id')) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="biaya_{{ $item->id }}">
                                                            <strong>{{ $item->nama }}</strong>
                                                            <br>
                                                            <span class="text-primary">{{ formatRupiah($item->total_tagihan) }}</span>
                                                        </label>
                                                    </div>
                                                    
                                                    @if($item->children->count() > 0)
                                                        <div class="mt-2 ms-4">
                                                            <small class="text-muted">Rincian:</small>
                                                            <ul class="list-unstyled ms-3">
                                                                @foreach($item->children as $child)
                                                                    <li>
                                                                        <small>• {{ $child->nama }}: {{ formatRupiah($child->jumlah) }}</small>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('biaya_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Angkatan <small class="text-muted">(opsional)</small></label>
                                <select name="angkatan" class="form-control @error('angkatan') is-invalid @enderror">
                                    <option value="">Pilih Angkatan</option>
                                    @foreach ($angkatan as $item)
                                        <option value="{{ $item }}"
                                            {{ old('angkatan') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('angkatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jurusan <small class="text-muted">(opsional)</small></label>
                                <select name="jurusan" class="form-control @error('jurusan') is-invalid @enderror">
                                    <option value="">Semua Jurusan</option>
                                    @foreach ($jurusan as $kode => $nama)
                                        <option value="{{ $kode }}"
                                            {{ old('jurusan') == $kode ? 'selected' : '' }}>
                                            {{ $nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jurusan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kelas <small class="text-muted">(opsional)</small></label>
                                <select name="kelas" class="form-control @error('kelas') is-invalid @enderror">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelas as $item)
                                        <option value="{{ $item }}" {{ old('kelas') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Tagihan</label>
                                <input type="date" name="tanggal_tagihan"
                                    class="form-control @error('tanggal_tagihan') is-invalid @enderror"
                                    value="{{ date('Y-m-01') }}">
                                @error('tanggal_tagihan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tanggal_jatuh_tempo"
                                    class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror"
                                    value="{{ date('Y-m-28') }}">
                                @error('tanggal_jatuh_tempo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ $button }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

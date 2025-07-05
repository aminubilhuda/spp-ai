@extends('layouts.app_sneat', ['title' => 'Pengaturan Instansi'])

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
                <h5 class="card-header">Pengaturan Instansi</h5>

                <div class="card-body">
                    <form action="{{ route('setting.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3 form-group">
                            <label for="nama_instansi" class="form-label">Nama Instansi</label>
                            <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" 
                                value="{{ old('nama_instansi', $settings->nama_instansi ?? '') }}" autofocus>
                            <span class="text-danger">{{ $errors->first('nama_instansi') }}</span>
                        </div>
                        
                        <div class="mb-3 form-group">
                            <label for="email_instansi" class="form-label">Email Instansi</label>
                            <input type="email" class="form-control" id="email_instansi" name="email_instansi"
                                value="{{ old('email_instansi', $settings->email_instansi ?? '') }}">
                            <span class="text-danger">{{ $errors->first('email_instansi') }}</span>
                        </div>
                        
                        <div class="mb-3 form-group">
                            <label for="nomor_wa_instansi" class="form-label">Nomor WhatsApp Instansi</label>
                            <input type="text" class="form-control" id="nomor_wa_instansi" name="nomor_wa_instansi"
                                value="{{ old('nomor_wa_instansi', $settings->nomor_wa_instansi ?? '') }}"
                                placeholder="Contoh: 081234567890">
                            <small class="form-text text-muted">Masukkan nomor tanpa tanda + atau kode negara</small>
                            <span class="text-danger">{{ $errors->first('nomor_wa_instansi') }}</span>
                        </div>
                        
                        <div class="mb-3 form-group">
                            <label for="alamat_instansi" class="form-label">Alamat Instansi</label>
                            <textarea class="form-control" id="alamat_instansi" name="alamat_instansi" 
                                rows="4" placeholder="Masukkan alamat lengkap instansi">{{ old('alamat_instansi', $settings->alamat_instansi ?? '') }}</textarea>
                            <span class="text-danger">{{ $errors->first('alamat_instansi') }}</span>
                        </div>
                        
                        <div class="mb-3 form-group">
                            <label for="logo_instansi" class="form-label">Logo Instansi</label>
                            <input type="file" class="form-control" id="logo_instansi" name="logo_instansi" accept="image/*">
                            @if (!empty($settings->logo_instansi))
                                <div class="mt-2">
                                    <img src="{{ Storage::disk('public')->url($settings->logo_instansi) }}" alt="Logo Instansi" style="max-height: 80px;">
                                </div>
                            @endif
                            <span class="text-danger">{{ $errors->first('logo_instansi') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Format nomor WhatsApp
    document.getElementById('nomor_wa_instansi').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Hapus semua karakter non-digit
        e.target.value = value;
    });
</script>
@endpush

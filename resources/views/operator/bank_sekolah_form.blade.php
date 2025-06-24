@extends('layouts.app_sneat')

@section('content')
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
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bankSelect = document.getElementById('bank_id');
            const kodeBankInput = document.getElementById('kode_bank');
            const namaBankInput = document.getElementById('nama_bank');

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
        });
    </script>
@endsection

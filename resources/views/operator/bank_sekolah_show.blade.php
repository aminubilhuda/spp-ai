@extends('layouts.app_sneat', ['title' => 'Bank Sekolah'])

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">{{ $title }}</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>Kode Bank</strong></td>
                                    <td width="5%">:</td>
                                    <td>{{ $model->kode_bank }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Bank</strong></td>
                                    <td>:</td>
                                    <td>{{ $model->nama_bank }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nomor Rekening</strong></td>
                                    <td>:</td>
                                    <td>{{ $model->no_rekening }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Atas Nama</strong></td>
                                    <td>:</td>
                                    <td>{{ $model->atas_nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Keterangan</strong></td>
                                    <td>:</td>
                                    <td>{{ $model->keterangan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat Pada</strong></td>
                                    <td>:</td>
                                    <td>{{ $model->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Diupdate Pada</strong></td>
                                    <td>:</td>
                                    <td>{{ $model->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('bank-sekolah.edit', $model->id) }}" class="btn btn-warning">
                            <i class="menu-icon tf-icons bx bx-edit"></i> Edit
                        </a>
                        <a href="{{ route('bank-sekolah.index') }}" class="btn btn-secondary">
                            <i class="menu-icon tf-icons bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

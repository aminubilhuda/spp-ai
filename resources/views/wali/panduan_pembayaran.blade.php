@extends('layouts.app_sneat_wali')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">{{ $title }}</h5>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6>Informasi Tagihan</h6>
                            <hr>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="150">Nama Siswa</td>
                                    <td>: {{ $tagihan->siswa->nama }}</td>
                                </tr>
                                <tr>
                                    <td>Total Tagihan</td>
                                    <td>: {{ formatRupiah($tagihan->tagihan_details->sum('jumlah_biaya')) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-primary">
                            <h6>Daftar Rekening Bank Sekolah</h6>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Bank</th>
                                            <th>Nomor Rekening</th>
                                            <th>Atas Nama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bankSekolah as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->bank->nama_bank }}</td>
                                            <td>{{ $item->nomor_rekening }}</td>
                                            <td>{{ $item->nama_rekening }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="accordion" id="panduanPembayaran">
                            <!-- ATM -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelAtm">
                                        <i class="bx bx-credit-card me-2"></i>Pembayaran Melalui ATM
                                    </button>
                                </h2>
                                <div id="panelAtm" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Masukkan Kartu ATM & PIN</li>
                                            <li>Pilih Menu "Transaksi Lainnya"</li>
                                            <li>Pilih Menu "Transfer"</li>
                                            <li>Pilih Bank Tujuan (jika beda bank)</li>
                                            <li>Masukkan Nomor Rekening Tujuan</li>
                                            <li>Masukkan Jumlah Transfer: <strong>{{ formatRupiah($tagihan->tagihan_details->sum('jumlah_biaya')) }}</strong></li>
                                            <li>Periksa kembali data transfer</li>
                                            <li>Jika sudah benar, tekan "YA" atau "OK"</li>
                                            <li>Simpan struk/bukti transfer</li>
                                            <li>Upload bukti transfer di menu pembayaran</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Internet Banking -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelIbanking">
                                        <i class="bx bx-globe me-2"></i>Pembayaran Melalui Internet Banking
                                    </button>
                                </h2>
                                <div id="panelIbanking" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Login ke Internet Banking</li>
                                            <li>Pilih Menu "Transfer"</li>
                                            <li>Pilih Bank Tujuan (jika beda bank)</li>
                                            <li>Masukkan Nomor Rekening Tujuan</li>
                                            <li>Masukkan Nominal Transfer: <strong>{{ formatRupiah($tagihan->tagihan_details->sum('jumlah_biaya')) }}</strong></li>
                                            <li>Masukkan Berita Transfer (opsional): Pembayaran SPP {{ $tagihan->siswa->nama }}</li>
                                            <li>Periksa kembali detail transfer</li>
                                            <li>Masukkan kode OTP/Token</li>
                                            <li>Simpan atau screenshot bukti transfer</li>
                                            <li>Upload bukti transfer di menu pembayaran</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <h6><i class="bx bx-info-circle me-2"></i>Penting!</h6>
                            <hr>
                            <ol>
                                <li>Pastikan nominal transfer sesuai dengan total tagihan</li>
                                <li>Simpan bukti pembayaran</li>
                                <li>Upload bukti pembayaran melalui menu pembayaran</li>
                                <li>Pembayaran akan diverifikasi oleh admin dalam 1x24 jam</li>
                                <li>Jika ada kendala, silahkan hubungi admin</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
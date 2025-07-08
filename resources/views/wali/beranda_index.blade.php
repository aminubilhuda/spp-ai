@extends('layouts.app_sneat_wali')

@section('content')
<div class="row">
    <!-- Kartu SPP -->
    <div class="col-md-7 mb-4">
        <div class="card">
            <div class="card-header fw-bold">KARTU SPP {{ strtoupper($siswa->nama) }}</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(bulanSPP() as $bulan)
                            @php
                                $namaBulan = \Carbon\Carbon::create(null, $bulan, 1)->locale('id')->translatedFormat('F');
                                $status = $status_per_bulan[$bulan] ?? 'BELUM BAYAR';
                            @endphp
                            <tr>
                                <td>{{ $namaBulan }}</td>
                                <td>
                                    @if($status == 'LUNAS')
                                        <span class="badge bg-success">LUNAS</span>
                                    @else
                                        <span class="badge bg-danger">BELUM BAYAR</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Notifikasi Baru -->
    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header fw-bold">Notifikasi Baru</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($notifikasi as $notif)
                    <li class="list-group-item">
                        <div class="fw-semibold">{{ $notif->data['title'] ?? '-' }}</div>
                        <a href="{{ $notif->data['url'] ?? '#' }}" class="text-primary">
                            {{ $notif->data['message'] ?? '-' }}
                        </a>
                        <div class="small text-muted">{{ $notif->created_at->diffForHumans() }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Tidak ada notifikasi baru</li>
                @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app_sneat', ['title' => 'Notifikasi'])

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    @if($notifications->where('read_at', null)->count() > 0)
                        <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                            <i class="bx bx-check-double me-1"></i>
                            Tandai Semua Dibaca
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Pesan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        <tr class="{{ $notification->read_at ? '' : 'table-warning' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $notification->data['title'] ?? 'Pembayaran Baru' }}</strong>
                                                @if($notification->read_at === null)
                                                    <span class="badge rounded-pill badge-xs bg-danger ms-1">Baru</span>
                                                @endif
                                            </td>
                                            <td>{{ $notification->data['message'] ?? 'Ada pembayaran baru yang menunggu konfirmasi' }}</td>
                                            <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($notification->read_at)
                                                    <span class="badge bg-success">Dibaca</span>
                                                @else
                                                    <span class="badge bg-warning">Belum Dibaca</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($notification->read_at === null)
                                                    <button class="btn btn-sm btn-outline-success" onclick="markAsRead('{{ $notification->id }}')">
                                                        <i class="bx bx-check me-1"></i>
                                                        Tandai Dibaca
                                                    </button>
                                                @endif
                                                @if(isset($notification->data['pembayaran_id']))
                                                    <a href="{{ route('pembayaran.index', ['search' => $notification->data['siswa_nama'] ?? '']) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="bx bx-show me-1"></i>
                                                        Lihat Pembayaran
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-bell bx-lg text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada notifikasi</h5>
                            <p class="text-muted">Anda belum memiliki notifikasi apapun.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function markAsRead(notificationId) {
            $.ajax({
                url: '/operator/notifications/' + notificationId + '/mark-as-read',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }

        function markAllAsRead() {
            if (confirm('Apakah Anda yakin ingin menandai semua notifikasi sebagai dibaca?')) {
                $.ajax({
                    url: '/operator/notifications/mark-all-as-read',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        }
    </script>
@endpush 
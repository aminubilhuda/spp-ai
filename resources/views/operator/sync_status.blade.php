@extends('layouts.app_sneat')

@section('title', 'Status Sinkronisasi Database')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="card">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                <span>Status Sinkronisasi Database</span>
                <span>
                    <button type="button" class="btn btn-primary btn-sm me-1" onclick="checkConnection()">
                        <i class="fas fa-wifi"></i> Cek Koneksi
                    </button>
                    <button type="button" class="btn btn-success btn-sm me-1" onclick="manualSync()">
                        <i class="fas fa-sync"></i> Sync Manual
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="resetFailed()">
                        <i class="fas fa-redo"></i> Reset Failed
                    </button>
                </span>
            </h5>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-wifi"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Status Koneksi Internet</span>
                                <span class="info-box-number" id="connection-status">Mengecek...</span>
                                <small id="connection-time"></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Terakhir Update</span>
                                <span class="info-box-number" id="last-update">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="mb-2"><i class="fas fa-database"></i> Master Data</h6>
                        <div class="row" id="master-data-stats"></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="mb-2"><i class="fas fa-exchange-alt"></i> Transaction Data</h6>
                        <div class="row" id="transaction-data-stats"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="sync-details-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tabel</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Pending</th>
                                        <th>Synced</th>
                                        <th>Failed</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="sync-details-body">
                                    <!-- Details will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-2">Log Sinkronisasi</h6>
                        <div class="sync-log" id="sync-log" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; font-family: monospace; font-size: 12px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Memproses...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let syncData = {};

// Load sync status on page load
$(document).ready(function() {
    loadSyncStatus();
    checkConnection();
    
    // Auto refresh every 30 seconds
    setInterval(function() {
        loadSyncStatus();
        checkConnection();
    }, 30000);
});

function loadSyncStatus() {
    $.ajax({
        url: '{{ route("sync.status.data") }}',
        method: 'GET',
        success: function(response) {
            syncData = response;
            updateStats(response.stats);
            updateDetails(response.stats);
            updateLog(response.log);
            $('#last-update').text(new Date().toLocaleString('id-ID'));
        },
        error: function(xhr) {
            console.error('Error loading sync status:', xhr);
            showAlert('error', 'Gagal memuat status sinkronisasi');
        }
    });
}

function updateStats(stats) {
    // Master Data Stats
    const masterDataStats = $('#master-data-stats');
    masterDataStats.empty();
    
    const masterTables = [
        { key: 'users', name: 'Users', icon: 'fas fa-users' },
        { key: 'settings', name: 'Settings', icon: 'fas fa-cog' },
        { key: 'tahun_pelajarans', name: 'Tahun Pelajaran', icon: 'fas fa-calendar' },
        { key: 'jurusans', name: 'Jurusan', icon: 'fas fa-graduation-cap' },
        { key: 'biayas', name: 'Biaya', icon: 'fas fa-money-bill' },
        { key: 'bank_sekolahs', name: 'Bank Sekolah', icon: 'fas fa-university' },
        { key: 'banks', name: 'Bank', icon: 'fas fa-credit-card' },
        { key: 'instansi_settings', name: 'Instansi Setting', icon: 'fas fa-building' }
    ];

    masterTables.forEach(table => {
        const stat = stats[table.key] || { total: 0, pending: 0, synced: 0, failed: 0 };
        const card = createStatCard(table, stat, 'master');
        masterDataStats.append(card);
    });

    // Transaction Data Stats
    const transactionDataStats = $('#transaction-data-stats');
    transactionDataStats.empty();
    
    const transactionTables = [
        { key: 'pembayarans', name: 'Pembayaran', icon: 'fas fa-credit-card' },
        { key: 'tagihans', name: 'Tagihan', icon: 'fas fa-file-invoice' },
        { key: 'tagihan_details', name: 'Tagihan Detail', icon: 'fas fa-list' },
        { key: 'siswas', name: 'Siswa', icon: 'fas fa-user-graduate' },
        { key: 'pengeluaran_kas', name: 'Pengeluaran Kas', icon: 'fas fa-cash-register' }
    ];

    transactionTables.forEach(table => {
        const stat = stats[table.key] || { total: 0, pending: 0, synced: 0, failed: 0 };
        const card = createStatCard(table, stat, 'transaction');
        transactionDataStats.append(card);
    });
}

function createStatCard(table, stat, type) {
    const pendingPercent = stat.total > 0 ? Math.round((stat.pending / stat.total) * 100) : 0;
    const syncedPercent = stat.total > 0 ? Math.round((stat.synced / stat.total) * 100) : 0;
    const failedPercent = stat.total > 0 ? Math.round((stat.failed / stat.total) * 100) : 0;
    
    const bgClass = stat.failed > 0 ? 'bg-danger' : (stat.pending > 0 ? 'bg-warning' : 'bg-success');
    
    return `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card ${bgClass} text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">
                                <i class="${table.icon}"></i> ${table.name}
                            </h6>
                            <p class="card-text mb-1">Total: ${stat.total}</p>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-light" onclick="syncTable('${table.key}')" title="Sync ${table.name}">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar bg-warning" style="width: ${pendingPercent}%" title="Pending: ${stat.pending}"></div>
                        <div class="progress-bar bg-success" style="width: ${syncedPercent}%" title="Synced: ${stat.synced}"></div>
                        <div class="progress-bar bg-danger" style="width: ${failedPercent}%" title="Failed: ${stat.failed}"></div>
                    </div>
                    <small class="mt-1 d-block">
                        P: ${stat.pending} | S: ${stat.synced} | F: ${stat.failed}
                    </small>
                </div>
            </div>
        </div>
    `;
}

function updateDetails(stats) {
    const tbody = $('#sync-details-body');
    tbody.empty();
    
    const allTables = [
        { key: 'users', name: 'Users' },
        { key: 'settings', name: 'Settings' },
        { key: 'tahun_pelajarans', name: 'Tahun Pelajaran' },
        { key: 'jurusans', name: 'Jurusan' },
        { key: 'biayas', name: 'Biaya' },
        { key: 'bank_sekolahs', name: 'Bank Sekolah' },
        { key: 'banks', name: 'Bank' },
        { key: 'instansi_settings', name: 'Instansi Setting' },
        { key: 'pembayarans', name: 'Pembayaran' },
        { key: 'tagihans', name: 'Tagihan' },
        { key: 'tagihan_details', name: 'Tagihan Detail' },
        { key: 'siswas', name: 'Siswa' },
        { key: 'pengeluaran_kas', name: 'Pengeluaran Kas' }
    ];

    allTables.forEach(table => {
        const stat = stats[table.key] || { total: 0, pending: 0, synced: 0, failed: 0 };
        const statusClass = stat.failed > 0 ? 'text-danger' : (stat.pending > 0 ? 'text-warning' : 'text-success');
        const statusText = stat.failed > 0 ? 'Ada Error' : (stat.pending > 0 ? 'Pending' : 'Synced');
        
        const row = `
            <tr>
                <td>${table.name}</td>
                <td><span class="${statusClass}">${statusText}</span></td>
                <td>${stat.total}</td>
                <td><span class="badge badge-warning">${stat.pending}</span></td>
                <td><span class="badge badge-success">${stat.synced}</span></td>
                <td><span class="badge badge-danger">${stat.failed}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="syncTable('${table.key}')">
                        <i class="fas fa-sync"></i> Sync
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateLog(log) {
    const logContainer = $('#sync-log');
    if (log && log.length > 0) {
        const logHtml = log.map(line => `<div>${line}</div>`).join('');
        logContainer.html(logHtml);
        logContainer.scrollTop(logContainer[0].scrollHeight);
    } else {
        logContainer.html('<div class="text-muted">Belum ada log sinkronisasi</div>');
    }
}

function checkConnection() {
    $('#connection-status').text('Mengecek...');
    
    $.ajax({
        url: '{{ route("sync.check-connection") }}',
        method: 'GET',
        success: function(response) {
            if (response.connected) {
                $('#connection-status').text('Online').removeClass('text-danger').addClass('text-success');
            } else {
                $('#connection-status').text('Offline').removeClass('text-success').addClass('text-danger');
            }
            $('#connection-time').text(new Date(response.timestamp).toLocaleString('id-ID'));
        },
        error: function() {
            $('#connection-status').text('Error').removeClass('text-success').addClass('text-danger');
        }
    });
}

function manualSync() {
    if (!confirm('Yakin ingin melakukan sinkronisasi manual?')) return;
    
    showLoading();
    
    $.ajax({
        url: '{{ route("sync.manual") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                loadSyncStatus();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showAlert('error', 'Terjadi kesalahan saat sinkronisasi');
        }
    });
}

function syncTable(tableName) {
    if (!confirm(`Yakin ingin sinkronisasi tabel ${tableName}?`)) return;
    
    showLoading();
    
    $.ajax({
        url: `{{ route("sync.table", ":table") }}`.replace(':table', tableName),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                loadSyncStatus();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showAlert('error', 'Terjadi kesalahan saat sinkronisasi tabel');
        }
    });
}

function resetFailed() {
    if (!confirm('Yakin ingin reset semua status sync yang failed?')) return;
    
    showLoading();
    
    $.ajax({
        url: '{{ route("sync.reset-failed") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                loadSyncStatus();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            showAlert('error', 'Terjadi kesalahan saat reset status');
        }
    });
}

function showLoading() {
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('.card-body').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.info-box {
    display: block;
    min-height: 80px;
    background: #fff;
    width: 100%;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}

.info-box-icon {
    border-radius: 0.25rem;
    display: block;
    float: left;
    height: 80px;
    width: 80px;
    text-align: center;
    font-size: 1.875rem;
    line-height: 80px;
    background: rgba(0,0,0,.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 80px;
}

.info-box-text {
    display: block;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}

.info-box-number {
    display: block;
    font-weight: 700;
}

.sync-log {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}

.progress {
    background-color: rgba(255,255,255,0.3);
}

.card.bg-success .progress-bar.bg-success {
    background-color: rgba(255,255,255,0.8) !important;
}

.card.bg-warning .progress-bar.bg-warning {
    background-color: rgba(255,255,255,0.8) !important;
}

.card.bg-danger .progress-bar.bg-danger {
    background-color: rgba(255,255,255,0.8) !important;
}
</style>
@endpush 
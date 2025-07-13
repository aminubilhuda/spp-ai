<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'users',
            'settings',
            'tahun_pelajarans',
            'jurusans',
            'biayas',
            'bank_sekolahs',
            'banks',
            'instansi_settings',
            'siswas',
            'tagihans',
            'tagihan_details',
            'pembayarans',
            'pengeluaran_kas',
            'statuses',
            'notifications',
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tbl) use ($table) {
                if (!Schema::hasColumn($table, 'sync_id')) {
                    $tbl->string('sync_id')->nullable()->unique();
                }
                if (!Schema::hasColumn($table, 'synced_at')) {
                    $tbl->timestamp('synced_at')->nullable();
                }
                if (!Schema::hasColumn($table, 'sync_status')) {
                    $tbl->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
                }
                if (!Schema::hasColumn($table, 'source_system')) {
                    $tbl->string('source_system')->default('local');
                }
            });
        }
    }

    public function down()
    {
        $tables = [
            'users',
            'settings',
            'tahun_pelajarans',
            'jurusans',
            'biayas',
            'bank_sekolahs',
            'banks',
            'instansi_settings',
            'siswas',
            'tagihans',
            'tagihan_details',
            'pembayarans',
            'pengeluaran_kas',
            'statuses',
            'notifications',
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tbl) use ($table) {
                if (Schema::hasColumn($table, 'sync_id')) {
                    $tbl->dropColumn('sync_id');
                }
                if (Schema::hasColumn($table, 'synced_at')) {
                    $tbl->dropColumn('synced_at');
                }
                if (Schema::hasColumn($table, 'sync_status')) {
                    $tbl->dropColumn('sync_status');
                }
                if (Schema::hasColumn($table, 'source_system')) {
                    $tbl->dropColumn('source_system');
                }
            });
        }
    }
}; 
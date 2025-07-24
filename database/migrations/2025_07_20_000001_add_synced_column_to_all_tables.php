<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncedColumnToAllTables extends Migration
{
    protected $tables = [
        'pembayarans',
        'tagihans',
        'tagihan_details',
        'siswas',
        'pengeluaran_kas',
        'users',
        'settings',
        'tahun_pelajarans',
        'jurusans',
        'biayas',
        'bank_sekolahs',
        'banks'
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'synced')) {
                        $table->boolean('synced')->default(false);
                    }
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('synced');
                });
            }
        }
    }
}

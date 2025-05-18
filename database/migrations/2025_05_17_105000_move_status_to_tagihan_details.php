<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column to tagihan_details
        Schema::table('tagihan_details', function (Blueprint $table) {
            $table->enum('status', ['baru','angsur','lunas', 'belum_lunas'])->default('baru')->after('jumlah_biaya');
        });

        // Copy status from tagihan to each of its details
        $tagihans = DB::table('tagihans')->get();
        foreach ($tagihans as $tagihan) {
            DB::table('tagihan_details')
                ->where('tagihan_id', $tagihan->id)
                ->update(['status' => $tagihan->status]);
        }

        // Remove status column from tagihans
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add status back to tagihans
        Schema::table('tagihans', function (Blueprint $table) {
            $table->enum('status', ['baru','angsur','lunas', 'belum_lunas'])->default('baru');
        });

        // Copy status from first detail to tagihan
        $details = DB::table('tagihan_details')
            ->select('tagihan_id', 'status')
            ->groupBy('tagihan_id')
            ->get();
        
        foreach ($details as $detail) {
            DB::table('tagihans')
                ->where('id', $detail->tagihan_id)
                ->update(['status' => $detail->status]);
        }

        // Remove status from tagihan_details
        Schema::table('tagihan_details', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

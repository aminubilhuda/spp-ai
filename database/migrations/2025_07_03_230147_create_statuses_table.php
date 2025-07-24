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
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('reason')->nullable();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->boolean('synced')->default(false);
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['model_type', 'model_id']);
            $table->index(['model_type', 'model_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
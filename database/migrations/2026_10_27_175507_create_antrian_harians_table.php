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
        Schema::create('antrian_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jadwal')->constrained('jadwal_periksa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedInteger('no_antrian_dipanggil')->nullable();
            $table->unique(['id_jadwal', 'tanggal']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian_harian');
    }
};

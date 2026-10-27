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
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->unique(
                ['id_jadwal', 'tanggal_janji', 'no_antrian'],
                'daftar_poli_schedule_date_queue_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->dropUnique('daftar_poli_schedule_date_queue_unique');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrian_harian', function (Blueprint $table) {
            $table->unsignedInteger('no_antrian_diperiksa')->nullable()->after('no_antrian_dipanggil');
        });
    }

    public function down(): void
    {
        Schema::table('antrian_harian', function (Blueprint $table) {
            $table->dropColumn('no_antrian_diperiksa');
        });
    }
};

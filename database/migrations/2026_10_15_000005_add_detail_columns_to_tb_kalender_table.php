<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_kalender', function (Blueprint $table) {
            $table->string('judul')->nullable()->after('id_leads');
            $table->string('jenis', 20)->default('lainnya')->after('judul');
            $table->string('lokasi')->nullable()->after('tanggal_kegiatan');
        });
    }

    public function down(): void
    {
        Schema::table('tb_kalender', function (Blueprint $table) {
            $table->dropColumn(['judul', 'jenis', 'lokasi']);
        });
    }
};

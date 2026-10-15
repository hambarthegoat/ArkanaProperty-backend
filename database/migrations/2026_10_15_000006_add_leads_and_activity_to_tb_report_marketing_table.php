<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_report_marketing', function (Blueprint $table) {
            $table->foreignId('id_leads')->nullable()->after('id_marketing')->constrained('tb_leads')->nullOnDelete();
            $table->string('jenis_aktivitas', 30)->nullable()->after('tanggal_report');
        });
    }

    public function down(): void
    {
        Schema::table('tb_report_marketing', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_leads');
            $table->dropColumn('jenis_aktivitas');
        });
    }
};

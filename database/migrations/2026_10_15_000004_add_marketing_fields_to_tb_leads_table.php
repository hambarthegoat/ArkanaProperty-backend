<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_leads', function (Blueprint $table) {
            $table->foreignId('id_tipe')->nullable()->after('id_perumahan')->constrained('tb_tipe_rumah')->nullOnDelete();
            $table->text('pesan')->nullable()->after('no_telp');
            $table->string('prioritas', 20)->nullable()->after('status_keseriusan');
            $table->text('catatan')->nullable()->after('prioritas');
        });
    }

    public function down(): void
    {
        Schema::table('tb_leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_tipe');
            $table->dropColumn(['pesan', 'prioritas', 'catatan']);
        });
    }
};

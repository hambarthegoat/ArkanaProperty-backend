<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_perumahan', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('deskripsi');
            $table->string('status', 20)->default('aktif')->after('gambar');
        });
    }

    public function down(): void
    {
        Schema::table('tb_perumahan', function (Blueprint $table) {
            $table->dropColumn(['gambar', 'status']);
        });
    }
};

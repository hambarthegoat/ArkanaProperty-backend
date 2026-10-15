<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_tipe_rumah', function (Blueprint $table) {
            $table->unsignedInteger('luas_tanah')->nullable()->after('cicilan');
            $table->unsignedInteger('luas_bangunan')->nullable()->after('luas_tanah');
            $table->unsignedTinyInteger('kamar_tidur')->nullable()->after('luas_bangunan');
            $table->unsignedTinyInteger('kamar_mandi')->nullable()->after('kamar_tidur');
            $table->string('gambar')->nullable()->after('spesifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('tb_tipe_rumah', function (Blueprint $table) {
            $table->dropColumn(['luas_tanah', 'luas_bangunan', 'kamar_tidur', 'kamar_mandi', 'gambar']);
        });
    }
};

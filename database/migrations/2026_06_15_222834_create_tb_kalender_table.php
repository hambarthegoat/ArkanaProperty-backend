<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_kalender', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_marketing')->constrained('tb_akun')->cascadeOnDelete();
            $table->foreignId('id_leads')->constrained('tb_akun')->cascadeOnDelete();
            $table->dateTime('tanggal_kegiatan');
            $table->text('deskripsi_agenda')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_kalender');
    }
};

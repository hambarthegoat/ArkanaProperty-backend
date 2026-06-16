<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_perumahan')->constrained('tb_perumahan')->cascadeOnDelete();
            $table->foreignId('id_marketing')->nullable()->constrained('tb_akun')->nullOnDelete();
            $table->foreignId('id_akun_leads')->nullable()->constrained('tb_akun')->nullOnDelete();
            $table->string('nama_calon');
            $table->string('no_telp');
            $table->string('status_keseriusan')->default('pending');
            $table->string('file_ktp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_leads');
    }
};

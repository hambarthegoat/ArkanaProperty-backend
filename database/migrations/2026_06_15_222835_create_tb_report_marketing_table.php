<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_report_marketing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_marketing')->constrained('tb_akun')->cascadeOnDelete();
            $table->date('tanggal_report');
            $table->text('deskripsi_progres')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_report_marketing');
    }
};

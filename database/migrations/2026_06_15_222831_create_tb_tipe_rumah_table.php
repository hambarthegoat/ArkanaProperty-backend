<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_tipe_rumah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_perumahan')->constrained('tb_perumahan')->cascadeOnDelete();
            $table->string('nama_tipe');
            $table->enum('jenis_rumah', ['subsidi', 'premium']);
            $table->decimal('harga', 15, 2);
            $table->decimal('dp', 15, 2)->nullable();
            $table->decimal('cicilan', 15, 2)->nullable();
            $table->text('spesifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_tipe_rumah');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_kavling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tipe')->constrained('tb_tipe_rumah')->cascadeOnDelete();
            $table->string('nomor_kavling');
            $table->enum('status', ['tersedia', 'terjual'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_kavling');
    }
};

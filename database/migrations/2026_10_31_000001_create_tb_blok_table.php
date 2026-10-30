<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blocks become real records so an admin can create a block before any
     * house exists in it. tb_kavling.blok stays a plain name; a block holds
     * the houses whose blok equals its name within the same perumahan.
     */
    public function up(): void
    {
        Schema::create('tb_blok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_perumahan')->constrained('tb_perumahan')->cascadeOnDelete();
            $table->string('nama', 50);
            $table->timestamps();

            $table->unique(['id_perumahan', 'nama']);
        });

        // Existing houses keep their blocks.
        $now = now();

        DB::table('tb_kavling')
            ->join('tb_tipe_rumah', 'tb_tipe_rumah.id', '=', 'tb_kavling.id_tipe')
            ->whereNotNull('tb_kavling.blok')
            ->where('tb_kavling.blok', '!=', '')
            ->select('tb_tipe_rumah.id_perumahan', 'tb_kavling.blok')
            ->distinct()
            ->get()
            ->each(fn ($row) => DB::table('tb_blok')->insert([
                'id_perumahan' => $row->id_perumahan,
                'nama' => $row->blok,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_blok');
    }
};

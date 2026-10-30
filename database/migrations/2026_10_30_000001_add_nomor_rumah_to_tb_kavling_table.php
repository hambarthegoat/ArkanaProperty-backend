<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A kavling (plot) can hold several houses. Each tb_kavling row is one
     * house with its own status and buyer; rows sharing perumahan, blok and
     * nomor_kavling belong to the same kavling and are told apart by
     * nomor_rumah. Existing single-house kavling keep nomor_rumah = null.
     */
    public function up(): void
    {
        Schema::table('tb_kavling', function (Blueprint $table) {
            $table->string('nomor_rumah', 20)->nullable()->after('nomor_kavling');
        });
    }

    public function down(): void
    {
        Schema::table('tb_kavling', function (Blueprint $table) {
            $table->dropColumn('nomor_rumah');
        });
    }
};

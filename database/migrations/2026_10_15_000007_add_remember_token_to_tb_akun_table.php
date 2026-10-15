<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_akun', function (Blueprint $table) {
            $table->rememberToken()->after('no_telp');
        });
    }

    public function down(): void
    {
        Schema::table('tb_akun', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status gains a "booking" state. The enum check constraint is dropped
     * in favour of a plain string validated by App\Enums\KavlingStatus, so
     * future states don't need a driver-specific enum rewrite.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tb_kavling DROP CONSTRAINT IF EXISTS tb_kavling_status_check');
        }

        Schema::table('tb_kavling', function (Blueprint $table) {
            $table->string('status', 20)->default('tersedia')->change();
        });

        Schema::table('tb_kavling', function (Blueprint $table) {
            $table->string('blok', 50)->nullable()->after('id_tipe');
            $table->foreignId('id_leads')->nullable()->after('status')->constrained('tb_leads')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tb_kavling', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_leads');
            $table->dropColumn('blok');
        });

        DB::table('tb_kavling')->where('status', 'booking')->update(['status' => 'tersedia']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tb_kavling ADD CONSTRAINT tb_kavling_status_check CHECK (status IN ('tersedia', 'terjual'))");

            return;
        }

        Schema::table('tb_kavling', function (Blueprint $table) {
            $table->enum('status', ['tersedia', 'terjual'])->default('tersedia')->change();
        });
    }
};

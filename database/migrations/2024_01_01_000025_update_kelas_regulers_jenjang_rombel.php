<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas_regulers', function (Blueprint $table) {
            if (Schema::hasColumn('kelas_regulers', 'jenjang')) {
                $table->integer('jenjang')->change();
            }
            if (Schema::hasColumn('kelas_regulers', 'tingkat')) {
                $table->string('tingkat', 20)->change();
            }
        });
    }

    public function down(): void
    {
        // Skip kalau tidak bisa rollback (data sudah integer, tidak bisa balik ke enum string)
        try {
            // Cek apakah data compatible dengan enum
            $incompatible = \DB::table('kelas_regulers')
                ->whereNotIn('jenjang', ['MI', 'MTs', 'MA', 'SMP', 'SMA'])
                ->exists();

            if ($incompatible) {
                // Update data incompatible ke default 'MI'
                \DB::table('kelas_regulers')
                    ->whereNotIn('jenjang', ['MI', 'MTs', 'MA', 'SMP', 'SMA'])
                    ->update(['jenjang' => 'MI']);
            }

            Schema::table('kelas_regulers', function (Blueprint $table) {
                $table->enum('jenjang', ['MI', 'MTs', 'MA', 'SMP', 'SMA'])->change();
                $table->integer('tingkat')->change();
            });
        } catch (\Throwable $e) {
            // Skip rollback kalau gagal
        }
    }
};

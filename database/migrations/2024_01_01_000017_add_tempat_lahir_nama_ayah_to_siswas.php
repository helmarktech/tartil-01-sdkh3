<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('tempat_lahir', 100)->nullable()->after('tanggal_lahir');
            $table->renameColumn('nama_ortu', 'nama_ayah');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('tempat_lahir');
            $table->renameColumn('nama_ayah', 'nama_ortu');
        });
    }
};

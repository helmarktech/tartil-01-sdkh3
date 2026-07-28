<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->string('materi', 255)->nullable()->after('halaman')->comment('Materi pembelajaran');
            $table->string('topik', 255)->nullable()->after('materi')->comment('Topik pembahasan');
            $table->string('rencana', 255)->nullable()->after('topik')->comment('Rencana pertemuan berikutnya');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->dropColumn(['materi', 'topik', 'rencana']);
        });
    }
};

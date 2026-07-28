<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapping surat-surat yang termasuk dalam setiap juz Al-Quran,
     * beserta rentang ayat dan total ayat per potongan juz.
     */
    public function up(): void
    {
        if (Schema::hasTable('juz_surats')) {
            return;
        }

        Schema::create('juz_surats', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('juz')->unsigned();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->integer('ayat_mulai')->unsigned()->default(1);
            $table->integer('ayat_selesai')->unsigned();
            $table->integer('total_ayat')->unsigned();
            $table->timestamps();

            $table->unique(['juz', 'surat_id', 'ayat_mulai']);
            $table->index('juz');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('juz_surats');
    }
};

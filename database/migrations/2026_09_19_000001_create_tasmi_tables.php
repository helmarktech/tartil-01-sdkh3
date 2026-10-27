<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel dokumentasi tasmi' siswa (video YouTube) beserta
     * interaksi like & komentar multi-peran (polimorfik).
     */
    public function up(): void
    {
        Schema::create('tasmi_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->unsignedTinyInteger('juz')->comment('Juz 1-30');
            $table->string('judul');
            $table->string('youtube_url');
            $table->string('youtube_id', 20);
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_tasmi')->nullable();
            $table->unsignedBigInteger('diupload_oleh')->nullable()->comment('ID admin (tabel users) pengunggah');
            $table->timestamps();

            $table->index('siswa_id');
            $table->index('juz');
        });

        Schema::create('tasmi_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasmi_video_id')->constrained('tasmi_videos')->cascadeOnDelete();
            $table->string('penyuka_type');
            $table->unsignedBigInteger('penyuka_id');
            $table->timestamps();

            $table->unique(['tasmi_video_id', 'penyuka_type', 'penyuka_id'], 'tasmi_like_unik');
            $table->index(['penyuka_type', 'penyuka_id']);
        });

        Schema::create('tasmi_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasmi_video_id')->constrained('tasmi_videos')->cascadeOnDelete();
            $table->string('pengomentar_type');
            $table->unsignedBigInteger('pengomentar_id');
            $table->text('isi');
            $table->timestamps();

            $table->index(['pengomentar_type', 'pengomentar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasmi_comments');
        Schema::dropIfExists('tasmi_likes');
        Schema::dropIfExists('tasmi_videos');
    }
};

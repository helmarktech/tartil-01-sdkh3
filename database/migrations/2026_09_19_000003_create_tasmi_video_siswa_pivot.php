<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu video tasmi' dapat memuat beberapa siswa (3-5 bahkan lebih).
     * Relasi siswa tunggal (tasmi_videos.siswa_id) dipindahkan ke tabel
     * pivot tasmi_video_siswa, lalu kolom lama dihapus.
     */
    public function up(): void
    {
        Schema::create('tasmi_video_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasmi_video_id')->constrained('tasmi_videos')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tasmi_video_id', 'siswa_id'], 'tasmi_video_siswa_unik');
        });

        // Backfill: pindahkan siswa tunggal yang sudah ada ke pivot
        DB::table('tasmi_videos')->select('id', 'siswa_id', 'created_at', 'updated_at')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                $insert = $rows->map(fn ($row) => [
                    'tasmi_video_id' => $row->id,
                    'siswa_id' => $row->siswa_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])->all();
                if ($insert) {
                    DB::table('tasmi_video_siswa')->insert($insert);
                }
            });

        Schema::table('tasmi_videos', function (Blueprint $table) {
            // SQLite (testing) mengabaikan dropForeign — tidak berpengaruh
            $table->dropForeign(['siswa_id']);
            // Index terpisah pada siswa_id harus dilepas dulu agar rebuild
            // tabel SQLite tidak gagal; di MySQL dropColumn melepasnya otomatis
            $table->dropIndex(['siswa_id']);
            $table->dropColumn('siswa_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasmi_videos', function (Blueprint $table) {
            $table->foreignId('siswa_id')->nullable()->after('id')
                ->constrained('siswas')->restrictOnDelete();
        });

        // Kembalikan siswa pertama tiap video ke kolom tunggal
        foreach (DB::table('tasmi_video_siswa')->orderBy('id')->get() as $pivot) {
            DB::table('tasmi_videos')
                ->where('id', $pivot->tasmi_video_id)
                ->whereNull('siswa_id')
                ->update(['siswa_id' => $pivot->siswa_id]);
        }

        Schema::dropIfExists('tasmi_video_siswa');
    }
};

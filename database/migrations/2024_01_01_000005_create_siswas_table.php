<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique(); // Nomor Induk Siswa
            $table->string('nama', 100);
            $table->string('no_hp', 15); // untuk login
            $table->string('password'); // hashed
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->foreignId('kelas_reguler_id')->nullable()->constrained('kelas_regulers')->nullOnDelete();
            $table->foreignId('kelas_tartil_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ortu', 100)->nullable();
            $table->string('no_hp_ortu', 15)->nullable();
            $table->date('tanggal_masuk');
            $table->enum('status', ['aktif', 'lulus', 'mutasi_keluar'])->default('aktif');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};

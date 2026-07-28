<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_tartils', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('nip', 30)->nullable()->unique();
            $table->string('email', 100)->unique();
            $table->string('no_hp', 15);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('guru_regulers', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('nip', 30)->nullable()->unique();
            $table->string('email', 100)->unique();
            $table->string('no_hp', 15);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_tartils');
        Schema::dropIfExists('guru_regulers');
    }
};

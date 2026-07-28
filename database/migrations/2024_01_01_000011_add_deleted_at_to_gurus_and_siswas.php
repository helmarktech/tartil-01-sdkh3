<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // softDeletes sudah ada di migration 000003 untuk guru_tartils dan guru_regulers
        // Tinggal siswas yang perlu softDeletes jika belum ada
        if (!Schema::hasColumn('siswas', 'deleted_at')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('siswas', 'deleted_at')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

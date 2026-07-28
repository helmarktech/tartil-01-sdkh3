<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Pulse\Support\PulseMigration;

return new class extends PulseMigration
{
    public function up(): void
    {
        if (!Schema::hasTable('pulse_values')) {
            Schema::create('pulse_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('timestamp');
                $table->string('type');
                $table->mediumText('key');
                match ($this->driver()) {
                    'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                    'pgsql' => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                    'sqlite' => $table->string('key_hash'),
                };
                $table->mediumText('value');
                $table->index(['type', 'key_hash', 'timestamp']);
            });
        }

        if (!Schema::hasTable('pulse_entries')) {
            Schema::create('pulse_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('timestamp');
                $table->string('type');
                $table->mediumText('key');
                match ($this->driver()) {
                    'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                    'pgsql' => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                    'sqlite' => $table->string('key_hash'),
                };
                $table->bigInteger('value')->nullable();
                $table->index(['type', 'key_hash', 'timestamp']);
                $table->index(['type', 'timestamp']);
                $table->index(['type', 'value', 'timestamp']);
            });
        }

        if (!Schema::hasTable('pulse_aggregates')) {
            Schema::create('pulse_aggregates', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('bucket');
                $table->unsignedMediumInteger('period');
                $table->string('type');
                $table->mediumText('key');
                match ($this->driver()) {
                    'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                    'pgsql' => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                    'sqlite' => $table->string('key_hash'),
                };
                $table->string('aggregate');
                $table->decimal('value', 20, 2);
                $table->unsignedInteger('count')->nullable();
                $table->unique(['bucket', 'period', 'type', 'key_hash', 'aggregate']);
                $table->index(['period', 'bucket']);
                $table->index(['type', 'period', 'bucket']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pulse_values');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_aggregates');
    }
};

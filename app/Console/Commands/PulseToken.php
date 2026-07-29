<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PulseToken extends Command
{
    protected $signature = 'pulse:token {--email=admin@tartil.id}';
    protected $description = 'Generate Pulse access token untuk email tertentu';

    public function handle(): int
    {
        $email = $this->option('email');
        $token = hash('sha256', $email . config('app.key'));

        $this->info("Email: {$email}");
        $this->info("Token: {$token}");
        $this->newLine();
        $this->info("URL akses:");
        $this->info(config('app.url') . '/pulse?pulse_token=' . $token);
        $this->info(config('app.url') . '/admin/pulse?pulse_token=' . $token);

        return 0;
    }
}

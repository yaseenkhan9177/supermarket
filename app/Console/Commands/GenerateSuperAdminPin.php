<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuperAdminPin;

class GenerateSuperAdminPin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'super-admin:generate-pin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a dynamic, one-time registration PIN for Super Admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Generate a random 6-digit PIN that does not already exist as active
        do {
            $pin = sprintf("%06d", mt_rand(100000, 999999));
        } while (SuperAdminPin::where('pin', $pin)->whereNull('used_at')->exists());

        SuperAdminPin::create([
            'pin' => $pin,
        ]);

        $this->info("Registration PIN generated successfully: {$pin}");
        $this->info("This PIN is for one-time use only.");
    }
}

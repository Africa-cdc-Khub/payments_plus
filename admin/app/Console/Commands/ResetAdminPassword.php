<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset admin password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $admin = Admin::where('username', $username)->first();

        if (!$admin) {
            $this->error("Admin with username '{$username}' not found!");
            return 1;
        }

        $admin->password = Hash::make($password);
        $admin->save();

        $this->info("Password for admin '{$username}' has been reset successfully!");
        $this->info("You can now login with:");
        $this->info("Username: {$username}");
        $this->info("Password: {$password}");

        return 0;
    }
}
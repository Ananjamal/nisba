<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;

class SyncRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync legacy role column with Spatie roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting role synchronization...');

        // 1. Ensure Roles exist
        if (Role::count() === 0) {
            $this->warn('No roles found. Running RolesAndPermissionsSeeder...');
            $seeder = new RolesAndPermissionsSeeder();
            $seeder->run();
        }

        // 2. Sync Users
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            if ($user->role === 'admin') {
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                    $count++;
                }
            } elseif ($user->role === 'affiliate') {
                if (!$user->hasRole('affiliate')) {
                    $user->assignRole('affiliate');
                    $count++;
                }
            }
        }

        $this->info("Successfully synchronized {$count} users.");
    }
}

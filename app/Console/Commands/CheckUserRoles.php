<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix user roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking user roles...');
        
        // Check if roles exist
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        
        if (!$adminRole) {
            $this->error('Admin role does not exist!');
            $this->info('Creating admin role...');
            $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        
        if (!$userRole) {
            $this->error('User role does not exist!');
            $this->info('Creating user role...');
            $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);
        }
        
        $this->info('Admin role ID: ' . $adminRole->id);
        $this->info('User role ID: ' . $userRole->id);
        
        // Check admin user
        $adminUser = User::where('email', 'rai3717@gmail.com')->first();
        
        if (!$adminUser) {
            $this->error('Admin user not found!');
            return;
        }
        
        $this->info('Admin user found: ' . $adminUser->email . ' (ID: ' . $adminUser->id . ')');
        
        // Check current roles
        $currentRoles = $adminUser->roles->pluck('name')->toArray();
        $this->info('Current roles: ' . implode(', ', $currentRoles));
        
        // Assign admin role if not present
        if (!$adminUser->hasRole('admin')) {
            $this->info('Assigning admin role...');
            $adminUser->assignRole('admin');
            $this->info('Admin role assigned successfully!');
        } else {
            $this->info('Admin role already assigned.');
        }
        
        // Verify role assignment
        $adminUser->refresh();
        $updatedRoles = $adminUser->roles->pluck('name')->toArray();
        $this->info('Updated roles: ' . implode(', ', $updatedRoles));
        
        // Check all users
        $this->info('\nAll users:');
        $users = User::all();
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->toArray();
            $this->info('- ' . $user->email . ' (ID: ' . $user->id . ') - Roles: ' . implode(', ', $roles));
        }
        
        $this->info('\nRole check completed!');
    }
}

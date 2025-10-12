<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user with proper role assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating admin user...');
        
        // Ensure admin role exists
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );
        
        $this->info('Admin role ID: ' . $adminRole->id);
        
        // Check if admin user already exists
        $adminUser = User::where('email', 'rai3717@gmail.com')->first();
        
        if ($adminUser) {
            $this->info('Admin user already exists: ' . $adminUser->email);
            
            // Remove all existing roles
            $adminUser->syncRoles([]);
            
            // Assign admin role
            $adminUser->assignRole('admin');
            
            $this->info('Admin role assigned to existing user.');
        } else {
            // Create new admin user
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'rai3717@gmail.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'status' => true,
            ]);
            
            $this->info('New admin user created: ' . $adminUser->email);
            
            // Assign admin role
            $adminUser->assignRole('admin');
            
            $this->info('Admin role assigned to new user.');
        }
        
        // Verify role assignment
        $adminUser->refresh();
        $roles = $adminUser->roles->pluck('name')->toArray();
        $this->info('User roles: ' . implode(', ', $roles));
        
        // Test role checking
        $hasAdminRole = $adminUser->hasRole('admin');
        $this->info('Has admin role: ' . ($hasAdminRole ? 'YES' : 'NO'));
        
        $this->info('Admin user creation completed!');
        $this->info('Login credentials:');
        $this->info('Email: rai3717@gmail.com');
        $this->info('Password: 123456');
    }
}

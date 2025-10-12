<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UpdateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user with rai3717@gmail.com exists
        $adminUser = User::where('email', 'rai3717@gmail.com')->first();
        
        if (!$adminUser) {
            // Create new admin user with rai3717@gmail.com
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'rai3717@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Default password
                'status' => true,
            ]);
            
            echo "Created new admin user: rai3717@gmail.com\n";
        } else {
            echo "Admin user already exists: rai3717@gmail.com\n";
        }
        
        // Assign admin role
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
            echo "Assigned admin role to user\n";
        } else {
            echo "User already has admin role\n";
        }
        
        // Also update existing admin@gmail.com user if it exists
        $oldAdmin = User::where('email', 'admin@gmail.com')->first();
        if ($oldAdmin) {
            $oldAdmin->update(['email' => 'rai3717@gmail.com']);
            echo "Updated admin@gmail.com to rai3717@gmail.com\n";
        }
    }
}

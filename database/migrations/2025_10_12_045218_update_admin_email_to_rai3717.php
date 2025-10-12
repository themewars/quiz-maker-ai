<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update admin user email from admin@gmail.com to rai3717@gmail.com
        DB::table('users')
            ->where('email', 'admin@gmail.com')
            ->update([
                'email' => 'rai3717@gmail.com',
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert admin user email back to admin@gmail.com
        DB::table('users')
            ->where('email', 'rai3717@gmail.com')
            ->update([
                'email' => 'admin@gmail.com',
                'updated_at' => now()
            ]);
    }
};

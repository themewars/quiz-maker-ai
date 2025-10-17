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
        // Update custom_legal_pages to fix pricing URL
        $settings = DB::table('settings')->first();
        if ($settings && $settings->custom_legal_pages) {
            $customPages = json_decode($settings->custom_legal_pages, true);
            
            if (is_array($customPages)) {
                $updated = false;
                foreach ($customPages as &$page) {
                    if (isset($page['slug']) && $page['slug'] === 'pricing') {
                        // Update the pricing page URL
                        $page['slug'] = 'pricing';
                        $updated = true;
                    }
                }
                
                if ($updated) {
                    DB::table('settings')->update([
                        'custom_legal_pages' => json_encode($customPages)
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this change
    }
};

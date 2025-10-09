<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing plans to enable PDF to Exam and Website to Exam features
        // This ensures that existing plans have these features enabled by default
        DB::table('plans')->update([
            'pdf_to_exam' => true,
            'website_to_exam' => true,
            'export_pdf' => true,
            'export_word' => true,
            'answer_key' => true,
            'share_results' => true,
            'email_participants' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Revert the changes if needed
        DB::table('plans')->update([
            'pdf_to_exam' => false,
            'website_to_exam' => false,
            'export_pdf' => false,
            'export_word' => false,
            'answer_key' => false,
            'share_results' => false,
            'email_participants' => false,
            'updated_at' => now(),
        ]);
    }
};

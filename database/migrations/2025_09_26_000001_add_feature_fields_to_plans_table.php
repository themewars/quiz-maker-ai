<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Limits
            $table->integer('exams_per_month')->nullable();
            $table->integer('max_questions_per_exam')->nullable();
            $table->integer('max_questions_per_month')->nullable();
            $table->integer('max_pdf_pages')->nullable();
            $table->integer('max_images_ocr')->nullable();
            $table->integer('max_website_tokens')->nullable();

            // Toggles
            $table->boolean('export_pdf')->default(false);
            $table->boolean('export_word')->default(false);
            $table->boolean('website_to_exam')->default(false);
            $table->boolean('pdf_to_exam')->default(false);
            $table->boolean('ppt_quiz')->default(false);
            $table->boolean('answer_key')->default(false);
            $table->boolean('white_label')->default(false);
            $table->boolean('watermark')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('multi_teacher')->default(false);

            // JSON: allowed question types
            $table->json('allowed_question_types')->nullable();

            // Badge & Payment
            $table->string('badge_text')->nullable();
            $table->string('payment_gateway_plan_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'exams_per_month',
                'max_questions_per_exam',
                'max_questions_per_month',
                'max_pdf_pages',
                'max_images_ocr',
                'max_website_tokens',
                'export_pdf',
                'export_word',
                'website_to_exam',
                'pdf_to_exam',
                'ppt_quiz',
                'answer_key',
                'white_label',
                'watermark',
                'priority_support',
                'multi_teacher',
                'allowed_question_types',
                'badge_text',
                'payment_gateway_plan_id',
            ]);
        });
    }
};



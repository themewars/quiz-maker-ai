<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\UserQuiz;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        $baseUrl = url('/');
        $languages = array_keys(getAllLanguages());

        // Public, active, non-expired quizzes
        $quizzes = Quiz::query()
            ->select(['id', 'unique_code', 'updated_at', 'quiz_expiry_date', 'status', 'is_public', 'is_show_home'])
            ->where('status', 1)
            ->where('is_public', 1)
            ->where(function ($query) {
                $query->whereNull('quiz_expiry_date')
                    ->orWhere('quiz_expiry_date', '>=', Carbon::now());
            })
            ->orderByDesc('id')
            ->limit(5000) // reasonable cap for sitemap
            ->get();

        $staticUrls = [
            route('home'),
            route('pricing'),
        ];

        // Recent public quiz results (leaderboards) by uuid, limited for size
        $results = UserQuiz::query()
            ->select(['uuid', 'updated_at'])
            ->whereNotNull('uuid')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get();

        // Render XML via Blade for readability
        return response()
            ->view('sitemap', [
                'baseUrl' => $baseUrl,
                'staticUrls' => $staticUrls,
                'quizzes' => $quizzes,
                'results' => $results,
                'languages' => $languages,
            ])
            ->header('Content-Type', 'application/xml');
    }
}



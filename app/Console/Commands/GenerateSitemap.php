<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quiz;
use App\Models\UserQuiz;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml into public/ including public exams and result pages';

    public function handle(): int
    {
        $baseUrl = url('/');
        $languages = array_keys(getAllLanguages());

        $static = [
            route('home') => ['priority' => '1.00'],
            route('pricing') => ['priority' => '0.80'],
            route('terms') => ['priority' => '0.80'],
            route('policy') => ['priority' => '0.80'],
            route('cookie') => ['priority' => '0.80'],
        ];

        if (function_exists('getSetting') && getSetting() && !empty(getSetting()->custom_legal_pages)) {
            foreach (getSetting()->custom_legal_pages as $page) {
                $static[route('custom.legal', $page['slug'])] = ['priority' => '0.80'];
            }
        }

        // Public, active, non-expired exams
        $quizzes = Quiz::query()
            ->select(['unique_code', 'updated_at', 'quiz_expiry_date'])
            ->where('status', 1)
            ->where('is_public', 1)
            ->where(function ($q) {
                $q->whereNull('quiz_expiry_date')->orWhere('quiz_expiry_date', '>=', Carbon::now());
            })
            ->orderByDesc('id')
            ->limit(5000)
            ->get();

        // Recent results
        $results = UserQuiz::query()
            ->select(['uuid', 'updated_at'])
            ->whereNotNull('uuid')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get();

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        foreach ($static as $url => $meta) {
            $xml[] = '<url>';
            $xml[] = '<loc>' . e($url) . '</loc>';
            foreach ($languages as $lang) {
                $alt = $url . '?lang=' . $lang;
                $xml[] = '<xhtml:link rel="alternate" hreflang="' . e($lang) . '" href="' . e($alt) . '" />';
            }
            $xml[] = '<changefreq>weekly</changefreq>';
            $xml[] = '<priority>' . $meta['priority'] . '</priority>';
            $xml[] = '</url>';
        }

        foreach ($quizzes as $quiz) {
            $url = route('quiz-player', ['code' => $quiz->unique_code]);
            $xml[] = '<url>';
            $xml[] = '<loc>' . e($url) . '</loc>';
            foreach ($languages as $lang) {
                $alt = $url . '?lang=' . $lang;
                $xml[] = '<xhtml:link rel="alternate" hreflang="' . e($lang) . '" href="' . e($alt) . '" />';
            }
            $xml[] = '<lastmod>' . optional($quiz->updated_at)->tz('UTC')->toAtomString() . '</lastmod>';
            $xml[] = '<changefreq>weekly</changefreq>';
            $xml[] = '<priority>0.60</priority>';
            $xml[] = '</url>';
        }

        foreach ($results as $res) {
            $url = route('show.quizResult', ['uuid' => $res->uuid]);
            $xml[] = '<url>';
            $xml[] = '<loc>' . e($url) . '</loc>';
            foreach ($languages as $lang) {
                $alt = $url . '?lang=' . $lang;
                $xml[] = '<xhtml:link rel="alternate" hreflang="' . e($lang) . '" href="' . e($alt) . '" />';
            }
            $xml[] = '<lastmod>' . optional($res->updated_at)->tz('UTC')->toAtomString() . '</lastmod>';
            $xml[] = '<changefreq>weekly</changefreq>';
            $xml[] = '<priority>0.50</priority>';
            $xml[] = '</url>';
        }

        $xml[] = '</urlset>';

        $path = public_path('sitemap.xml');
        file_put_contents($path, implode("\n", $xml));
        $this->info('Sitemap written to: ' . $path);

        return self::SUCCESS;
    }
}



<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Plan;
use App\Models\Quiz;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Subscription;
use Illuminate\Support\Facades\Schema;
use App\Enums\SubscriptionStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (isset(getSetting()->enable_landing_page) && getSetting()->enable_landing_page == 0) {
            if (Auth::check() && Auth::user()->hasRole('admin')) {
                return redirect()->route('filament.admin.pages.dashboard');
            }

            if (Auth::check() && Auth::user()->hasRole('user')) {
                return redirect()->route('filament.user.pages.dashboard');
            }

            return redirect()->route('filament.auth.auth.login');
        }
        // Pricing plans: show all active plans, sorted by sort_order (if present) else by price
        $plansQuery = Plan::query()->where('status', true);
        if (Schema::hasColumn('plans', 'sort_order')) {
            $plansQuery->orderBy('sort_order')->orderBy('price');
        } else {
            $plansQuery->orderBy('price');
        }
        $plans = $plansQuery->get();
        $testimonials = Testimonial::all();
        // Eager-load relationships used in the view to prevent N+1 and missing data
        $quizzes = Quiz::with(['category', 'user', 'questions'])
            ->whereNotNull('category_id')
            ->where('status', 1)->where('is_show_home', 1)->where('is_public', 1)
            ->where(function ($query) {
                $query->whereNull('quiz_expiry_date')
                    ->orWhere('quiz_expiry_date', '>=', Carbon::now());
            })
            ->orderBy('id', 'desc')
            ->get();
        $faqs = Faq::where('status', 1)->get();

        return view('home.index', compact('plans', 'testimonials', 'quizzes', 'faqs'));
    }

    public function terms()
    {
        $seeting = Setting::first();

        $terms = $seeting->terms_and_condition;

        return view('home.terms', compact('terms'));
    }

    public function policy()
    {
        $seeting = Setting::first();

        $policy = $seeting->privacy_policy;

        return view('home.policy', compact('policy'));
    }

    public function cookie()
    {
        $seeting = Setting::first();

        $cookie = $seeting->cookie_policy;

        return view('home.cookie', compact('cookie'));
    }

    public function customLegal($slug)
    {
        $setting = Setting::first();
        $customPages = $setting->custom_legal_pages ?? [];
        
        // Find the page with matching slug
        $page = collect($customPages)->firstWhere('slug', $slug);
        
        if (!$page) {
            abort(404, 'Legal page not found');
        }

        return view('home.custom-legal', compact('page'));
    }

    // public function index()
    // {
    //     /** @var User $user */
    //     $user = auth()->user();

    //     if ($user) {
    //         $role = $user->roles()->first();

    //         if ($role && $role->name === User::ADMIN_ROLE) {
    //             return redirect()->route('filament.admin.pages.dashboard');
    //         }

    //         if ($role && $role->name === User::USER_ROLE) {
    //             return redirect()->route('filament.user.pages.dashboard');
    //         }
    //     }

    //     return redirect()->route('filament.auth.auth.login');
    // }
}

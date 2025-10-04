<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Plan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('price')
            ->get();
            
        $faqs = Faq::where('status', 1)->get();
            
        return view('pricing.index', compact('plans', 'faqs'));
    }
}

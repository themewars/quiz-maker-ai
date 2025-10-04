<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('price')
            ->get();
            
        return view('pricing.index', compact('plans'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function switch(Request $request): JsonResponse
    {
        $currencyCode = strtoupper($request->get('currency'));
        
        // Validate currency exists
        $currency = Currency::where('code', $currencyCode)->first();
        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid currency selected'
            ], 400);
        }

        // Set currency in session
        session(['currency' => $currencyCode]);
        
        // Set cookie for persistence
        $cookie = cookie('currency', $currencyCode, 60 * 24 * 30); // 30 days

        return response()->json([
            'success' => true,
            'message' => 'Currency switched successfully',
            'currency' => [
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'name' => $currency->name
            ]
        ])->cookie($cookie);
    }

    public function getCurrent(): JsonResponse
    {
        $currency = getCurrentCurrency();
        
        return response()->json([
            'success' => true,
            'currency' => [
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'name' => $currency->name
            ]
        ]);
    }
}
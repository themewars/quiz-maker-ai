<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;

class ProgressController extends Controller
{
    public function show(Request $request, int $quizId)
    {
        // Optional authorization: only owner can view
        if (Auth::check()) {
            $quiz = Quiz::find($quizId);
            if ($quiz && $quiz->user_id !== Auth::id()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }
        $key = "quiz:{$quizId}:gen_progress";
        $data = Cache::get($key);
        
        // Debug logging
        \Log::info("Progress API called for quiz {$quizId}: " . json_encode($data));
        
        return response()->json($data ?? [
            'total' => 0,
            'done' => 0,
            'status' => 'idle',
        ]);
    }
}



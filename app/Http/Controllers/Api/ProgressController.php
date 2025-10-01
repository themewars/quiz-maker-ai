<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class ProgressController extends Controller
{
    public function show(Request $request, int $quizId)
    {
        $key = "quiz:{$quizId}:gen_progress";
        $data = Cache::get($key);
        return response()->json($data ?? [
            'total' => 0,
            'done' => 0,
            'status' => 'idle',
        ]);
    }
}



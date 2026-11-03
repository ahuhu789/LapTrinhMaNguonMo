<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiRecommendRequest;
use App\Services\AiRecommendationService;

class AiChatController extends Controller
{
    /**
     * API nhận câu hỏi từ Client và trả về gợi ý Talent từ AI
     */
    public function recommend(AiRecommendRequest $request, AiRecommendationService $aiService)
    {
        $result = $aiService->recommendTalents($request->validated('message'));

        return response()->json($result);
    }
}

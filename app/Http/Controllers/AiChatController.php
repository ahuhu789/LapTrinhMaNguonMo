<?php

namespace App\Http\Controllers;

use App\Services\AiRecommendationService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    /**
     * API nhận câu hỏi từ Client và trả về gợi ý Talent từ AI
     */
    public function recommend(Request $request, AiRecommendationService $aiService)
    {
        $request->validate([
            'message' => 'required|string|min:2|max:500',
        ], [
            'message.required' => 'Vui lòng nhập nhu cầu booking của bạn.',
            'message.min' => 'Nội dung quá ngắn.',
        ]);

        $result = $aiService->recommendTalents($request->message);

        return response()->json($result);
    }
}

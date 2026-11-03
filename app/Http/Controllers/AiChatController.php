<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiRecommendRequest;
use App\Models\Booking;
use Illuminate\Support\Facades\Gate;
use App\Services\AiRecommendationService;

class AiChatController extends Controller
{
    /**
     * API nhận câu hỏi từ Client và trả về gợi ý Talent từ AI
     */
    public function recommend(AiRecommendRequest $request, AiRecommendationService $aiService)
    {
        $result = $aiService->recommendTalents($request->validated('message'));

        // can_book: FE ẩn nút "Đặt lịch" trên thẻ talent nếu false (khách / streamer / manager / admin)
        return response()->json($result + ['can_book' => Gate::allows('create', Booking::class)]);
    }
}

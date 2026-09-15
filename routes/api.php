<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiChatController;
use App\Services\BookingCollisionService;
use App\Models\Schedule;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Chatbot AI
Route::post('/ai-recommend', [AiChatController::class, 'recommend']);

// API kiểm tra nhanh va chạm lịch trình (cho client-side AJAX live validation)
Route::post('/check-collision', function (Request $request, BookingCollisionService $collisionService) {
    $streamerId = (int) $request->input('streamer_id');
    $startTime = $request->input('start_time');
    $endTime = $request->input('end_time');

    if (!$streamerId || !$startTime || !$endTime) {
        return response()->json(['error' => 'Thiếu tham số'], 422);
    }

    $result = $collisionService->checkCollision($streamerId, $startTime, $endTime);
    return response()->json($result);
});

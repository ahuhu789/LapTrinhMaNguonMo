<?php

namespace App\Providers;

use App\Models\Booking;
use App\Services\AiRecommendationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // $canBook cho mọi view: true chỉ khi người dùng hiện tại là client (BookingPolicy@create).
        // Tương đương @can('create', App\Models\Booking::class) (chỉ kiểm tra role, không truy vấn DB).
        // $aiMode ('gemini' | 'fallback') theo cấu hình: FE chỉ ghi "Powered by Gemini" khi = 'gemini'.
        // Mỗi câu trả lời JSON của /ai/recommend có ai_mode THỰC (Gemini lỗi -> 'fallback').
        View::composer('*', function ($view) {
            $view->with('canBook', Gate::allows('create', Booking::class));
            $view->with('aiMode', AiRecommendationService::configuredMode());
        });

        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\StreamerProfile;
use App\Models\Booking;
use App\Models\StreamMetric;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Thống kê ấn tượng của MCN Platform
        $stats = [
            'total_talents' => StreamerProfile::where('status', 'active')->count(),
            'total_bookings' => Booking::whereIn('status', ['approved', 'completed'])->count(),
            'total_hours' => round(StreamMetric::sum('duration_hours')),
            'total_views' => StreamMetric::sum('avg_viewers'),
        ];

        // 2. Lấy top 6 streamer nổi bật nhất (theo lượt view trung bình)
        $featuredTalents = StreamerProfile::where('status', 'active')
            ->with(['metrics', 'manager'])
            ->get()
            ->sortByDesc('avg_viewers')
            ->take(6);

        // 3. Danh mục độc quyền
        $categories = StreamerProfile::select('category')
            ->distinct()
            ->pluck('category');

        return view('home', compact('stats', 'featuredTalents', 'categories'));
    }
}

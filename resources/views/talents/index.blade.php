@extends('layouts.app')

@section('title', 'Danh Mục Talent Streamers - MCN Platform')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Danh Mục Talent Streamers</h1>
        <p class="text-slate-500 text-sm mt-1">Khám phá và kết nối với các nhà sáng tạo nội dung livestream hàng đầu.</p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-8">
        <form action="{{ route('talents.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Keyword search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Tìm kiếm theo tên / từ khóa</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tên streamer, bio, game..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Thể loại</label>
                <select name="category" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tất cả thể loại</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Price Range -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Khung giá thuê</label>
                <select name="price_range" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Mọi mức giá</option>
                    <option value="under_1m" {{ request('price_range') == 'under_1m' ? 'selected' : '' }}>Dưới 1 triệu / giờ</option>
                    <option value="1m_to_2m" {{ request('price_range') == '1m_to_2m' ? 'selected' : '' }}>1 triệu - 2 triệu / giờ</option>
                    <option value="above_2m" {{ request('price_range') == 'above_2m' ? 'selected' : '' }}>Trên 2 triệu / giờ</option>
                </select>
            </div>

            <!-- Sort & Submit -->
            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Sắp xếp</label>
                    <select name="sort" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="views_desc" {{ request('sort') == 'views_desc' ? 'selected' : '' }}>View cao nhất</option>
                        <option value="rate_asc" {{ request('sort') == 'rate_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                        <option value="rate_desc" {{ request('sort') == 'rate_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                    </select>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm h-[38px] flex items-center justify-center">
                    <i class="fas fa-filter mr-1.5"></i> Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Talent Cards Grid -->
    @if($talents->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <i class="fas fa-user-slash text-4xl text-slate-300 mb-3"></i>
            <h3 class="text-lg font-bold text-slate-700">Không tìm thấy Talent phù hợp</h3>
            <p class="text-sm text-slate-500 mt-1">Vui lòng thử điều chỉnh lại bộ lọc hoặc từ khóa tìm kiếm.</p>
            <a href="{{ route('talents.index') }}" class="inline-block mt-4 text-xs font-bold text-primary-600 hover:underline">Xóa bộ lọc</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($talents as $talent)
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 overflow-hidden flex flex-col group">
                    <div class="h-28 bg-slate-200 relative overflow-hidden">
                        <img src="{{ $talent->banner_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800' }}" alt="Banner" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-white/90 text-slate-800 backdrop-blur-sm shadow-sm">
                            {{ $talent->category }}
                        </span>
                    </div>

                    <div class="p-5 pt-0 flex-1 flex flex-col relative">
                        <div class="flex justify-between items-end -mt-8 mb-2">
                            <img src="{{ $talent->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=200' }}" alt="{{ $talent->stage_name }}" class="w-16 h-16 rounded-2xl border-4 border-white shadow-md object-cover">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-medium">Giá tham khảo</span>
                                <span class="text-sm font-extrabold text-primary-600">{{ $talent->formatted_rate }}</span>
                            </div>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 flex items-center space-x-1">
                            <span>{{ $talent->stage_name }}</span>
                            <i class="fas fa-circle-check text-blue-500 text-xs" title="Verified Talent"></i>
                        </h3>

                        <p class="text-xs text-slate-500 line-clamp-2 mt-1 mb-3 flex-1">
                            {{ $talent->bio }}
                        </p>

                        <div class="bg-slate-50 rounded-xl p-2.5 grid grid-cols-2 gap-2 text-center text-xs mb-3 border border-slate-100">
                            <div>
                                <span class="text-slate-400 block text-[10px]">Avg Viewers</span>
                                <span class="font-bold text-slate-800">{{ number_format($talent->avg_viewers) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Peak Viewers</span>
                                <span class="font-bold text-indigo-600">{{ number_format($talent->peak_viewers) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('talents.show', $talent->id) }}" class="text-center py-2 px-2 rounded-xl border border-slate-300 hover:border-primary-500 hover:text-primary-600 font-semibold text-xs transition">
                                Media Kit
                            </a>
                            <a href="{{ route('bookings.create', ['streamer_id' => $talent->id]) }}" class="text-center py-2 px-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs transition shadow-sm">
                                Đặt Lịch
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

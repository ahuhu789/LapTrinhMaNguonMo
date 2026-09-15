@extends('layouts.streamer')

@section('title', 'Cập Nhật Media Kit - ' . $profile->stage_name)
@section('page_title', 'Chỉnh Sửa Hồ Sơ & Press Kit Cá Nhân')

@section('content')
<div class="max-w-3xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <div class="mb-6 pb-4 border-b border-slate-200">
        <h2 class="text-lg font-bold text-slate-900">Thông Tin Talent & Báo Giá Booking</h2>
        <p class="text-xs text-slate-500 mt-1">Các thông tin này sẽ được hiển thị công khai trên trang Public Media Kit để khách hàng tham khảo khi booking.</p>
    </div>

    <form action="{{ route('streamer.profile.update') }}" method="POST" class="space-y-5 text-sm">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nghệ Danh (Stage Name) <span class="text-rose-500">*</span></label>
                <input type="text" name="stage_name" value="{{ old('stage_name', $profile->stage_name) }}" required
                    class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Lĩnh Vực / Thể Loại Chính <span class="text-rose-500">*</span></label>
                <select name="category" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-medium">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $profile->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Mức Giá Booking Tham Khảo (VNĐ / Giờ) <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input type="number" name="rate_per_hour" value="{{ old('rate_per_hour', $profile->rate_per_hour) }}" min="100000" step="50000" required
                    class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-slate-800">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-semibold text-slate-400">VNĐ/h</span>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Tiểu Sử / Bio Giới Thiệu Bản Thân</label>
            <textarea name="bio" rows="4" class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                placeholder="Giới thiệu phong cách stream, tựa game sở trường, thế mạnh tương tác khán giả...">{{ old('bio', $profile->bio) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">URL Ảnh Đại Diện (Avatar)</label>
                <input type="url" name="avatar_url" value="{{ old('avatar_url', $profile->avatar_url) }}" placeholder="https://..."
                    class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">URL Ảnh Bìa (Banner)</label>
                <input type="url" name="banner_url" value="{{ old('banner_url', $profile->banner_url) }}" placeholder="https://..."
                    class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                <i class="fas fa-save mr-1.5"></i> Lưu Thông Tin Media Kit
            </button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Đăng Ký Tài Khoản Client - MCN Platform')

@section('content')
<div class="max-w-md mx-auto my-12 px-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 px-6 py-8 text-white text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 flex items-center justify-center text-2xl mb-3 shadow-inner">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="text-2xl font-bold">Đăng Ký Client / Nhãn Hàng</h2>
            <p class="text-primary-100 text-sm mt-1">Kết nối và booking Talent Streamers chuyên nghiệp</p>
        </div>

        <div class="p-6">
            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Tên đại diện hoặc Nhãn hàng</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                        placeholder="VD: Nguyễn Văn A (ASUS Brand)"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Địa chỉ Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        placeholder="booking@brand.vn"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Mật khẩu</label>
                    <input type="password" name="password" id="password" required
                        placeholder="Tối thiểu 6 ký tự"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        placeholder="Nhập lại mật khẩu"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>

                <div class="text-xs text-slate-500">
                    Bằng việc đăng ký, bạn đồng ý với Điều khoản dịch vụ và Chính sách bảo mật của MCN Platform.
                </div>

                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 rounded-xl shadow-md shadow-primary-500/20 transition">
                    Hoàn Tất Đăng Ký
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600 border-t border-slate-200 pt-4">
                Đã có tài khoản? 
                <a href="{{ route('login') }}" class="text-primary-600 font-bold hover:underline">Đăng nhập tại đây</a>
            </div>
        </div>
    </div>
</div>
@endsection

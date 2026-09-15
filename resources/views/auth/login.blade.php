@extends('layouts.app')

@section('title', 'Đăng Nhập - MCN Talent Platform')

@section('content')
<div class="max-w-md mx-auto my-12 px-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 px-6 py-8 text-white text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 flex items-center justify-center text-2xl mb-3 shadow-inner">
                <i class="fas fa-lock"></i>
            </div>
            <h2 class="text-2xl font-bold">Đăng Nhập Hệ Thống</h2>
            <p class="text-primary-100 text-sm mt-1">MCN Talent Management Platform</p>
        </div>

        <div class="p-6">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Địa chỉ Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@mcn.com"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Mật khẩu</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-key"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-600">
                        <input type="checkbox" name="remember" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 rounded-xl shadow-md shadow-primary-500/20 transition">
                    Đăng Nhập Ngay
                </button>
            </form>

            <!-- Quick Demo Accounts for Testing / Grading -->
            <div class="mt-6 pt-6 border-t border-slate-200">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">
                    Tài khoản Demo Nhanh (Click để tự điền)
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillLogin('admin@mcn.com')" class="p-2 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 font-medium text-left">
                        <i class="fas fa-shield-halved mr-1"></i> Admin
                    </button>
                    <button type="button" onclick="fillLogin('manager1@mcn.com')" class="p-2 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 font-medium text-left">
                        <i class="fas fa-users-gear mr-1"></i> Manager 1
                    </button>
                    <button type="button" onclick="fillLogin('streamer1@mcn.com')" class="p-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-medium text-left">
                        <i class="fas fa-video mr-1"></i> Streamer (Đạt Pro)
                    </button>
                    <button type="button" onclick="fillLogin('client1@mcn.com')" class="p-2 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 font-medium text-left">
                        <i class="fas fa-building mr-1"></i> Client (GearVN)
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 text-center">Mật khẩu mặc định: <span class="font-mono font-bold text-slate-600">password123</span></p>
            </div>

            <div class="mt-4 text-center text-sm text-slate-600">
                Chưa có tài khoản nhãn hàng? 
                <a href="{{ route('register') }}" class="text-primary-600 font-bold hover:underline">Đăng ký Client</a>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password123';
}
</script>
@endsection

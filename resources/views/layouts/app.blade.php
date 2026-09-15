<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MCN Platform - Hệ thống Quản lý Talent Streamers')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Header / Navbar -->
    <header class="bg-white/90 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-primary-500/30">
                            <i class="fas fa-satellite-dish text-lg"></i>
                        </div>
                        <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-primary-700 to-indigo-600 bg-clip-text text-transparent">
                            MCN TALENT
                        </span>
                    </a>
                    <nav class="hidden md:flex ml-8 space-x-6">
                        <a href="{{ route('home') }}" class="text-slate-600 hover:text-primary-600 font-medium text-sm transition">Trang Chủ</a>
                        <a href="{{ route('talents.index') }}" class="text-slate-600 hover:text-primary-600 font-medium text-sm transition">Danh Mục Talent</a>
                        <a href="{{ route('bookings.create') }}" class="text-slate-600 hover:text-primary-600 font-medium text-sm transition">Đặt Lịch Booking</a>
                        @auth
                            <a href="{{ route('bookings.index') }}" class="text-slate-600 hover:text-primary-600 font-medium text-sm transition">Quản Lý Booking</a>
                        @endauth
                    </nav>
                </div>

                <div class="flex items-center space-x-3">
                    @guest
                        <a href="{{ route('login') }}" class="text-slate-700 hover:text-primary-600 text-sm font-semibold px-4 py-2">Đăng Nhập</a>
                        <a href="{{ route('register') }}" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition">Đăng Ký Client</a>
                    @else
                        <!-- Dashboard button theo role -->
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="bg-purple-100 text-purple-700 hover:bg-purple-200 text-xs font-semibold px-3 py-1.5 rounded-lg">
                                <i class="fas fa-shield-halved mr-1"></i> Admin Panel
                            </a>
                        @elseif(Auth::user()->isManager())
                            <a href="{{ route('manager.dashboard') }}" class="bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-semibold px-3 py-1.5 rounded-lg">
                                <i class="fas fa-users-gear mr-1"></i> Manager Workspace
                            </a>
                        @elseif(Auth::user()->isStreamer())
                            <a href="{{ route('streamer.dashboard') }}" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 text-xs font-semibold px-3 py-1.5 rounded-lg">
                                <i class="fas fa-video mr-1"></i> Streamer Workspace
                            </a>
                        @endif

                        <div class="relative flex items-center space-x-2 pl-3 border-l border-slate-200">
                            <span class="text-sm font-medium text-slate-700 hidden sm:inline">{{ Auth::user()->name }}</span>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-rose-600 text-sm p-1.5 transition" title="Đăng xuất">
                                    <i class="fas fa-arrow-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center shadow-sm">
                <i class="fas fa-circle-check text-emerald-500 mr-3 text-lg"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center shadow-sm">
                <i class="fas fa-circle-exclamation text-rose-500 mr-3 text-lg"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 shadow-sm">
                <div class="font-semibold flex items-center mb-1">
                    <i class="fas fa-triangle-exclamation mr-2 text-amber-500"></i> Có lỗi xảy ra, vui lòng kiểm tra lại:
                </div>
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-slate-500">
            <p class="mb-2">© 2026 MCN Talent Management Platform. Đồ án môn Lập trình Web với PHP (Laravel Framework).</p>
            <p class="text-xs text-slate-400">Kiến trúc MVC • Phân quyền RBAC • Kiểm tra Trùng Lịch Collision-Free • AI Context Injection Recommendation</p>
        </div>
    </footer>

    <!-- AI Chatbot Floating Widget -->
    @include('layouts.ai_widget')

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/ai-chat.js') }}"></script>
    @stack('scripts')
</body>
</html>

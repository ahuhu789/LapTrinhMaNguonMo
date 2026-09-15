<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản Trị MCN Platform')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="bg-slate-100 text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0">
        <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
            <a href="{{ route('home') }}" class="flex items-center space-x-2 text-white font-black text-lg">
                <i class="fas fa-satellite-dish text-primary-500"></i>
                <span>MCN CONTROL</span>
            </a>
        </div>

        <div class="px-4 py-3 border-b border-slate-800/80 bg-slate-900/50">
            <div class="text-xs text-slate-400 uppercase font-semibold">Tài khoản</div>
            <div class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</div>
            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded {{ Auth::user()->isAdmin() ? 'bg-purple-900/80 text-purple-200 border border-purple-700' : 'bg-blue-900/80 text-blue-200 border border-blue-700' }}">
                {{ strtoupper(Auth::user()->role) }}
            </span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-gauge w-6"></i> Dashboard Admin
                </a>
            @endif

            @if(Auth::user()->isManager() || Auth::user()->isAdmin())
                <a href="{{ route('manager.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('manager.dashboard') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-chart-line w-6"></i> Dashboard Manager
                </a>
                <a href="{{ route('bookings.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bookings.*') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-calendar-check w-6"></i> Quản Lý Booking
                </a>
                <a href="{{ route('manager.kpis') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('manager.kpis*') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-bullseye w-6"></i> Thiết Lập & KPI
                </a>
                <a href="{{ route('manager.metrics.upload') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('manager.metrics.*') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-file-csv w-6"></i> Nhập CSV Metrics
                </a>
            @endif

            @if(Auth::user()->isAdmin())
                <div class="pt-4 pb-1 text-xs text-slate-400 font-semibold px-3 uppercase tracking-wider">Hệ Thống</div>
                <a href="{{ route('admin.users') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.users*') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-users w-6"></i> Quản Lý Tài Khoản
                </a>
                <a href="{{ route('admin.assign') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.assign*') ? 'bg-primary-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                    <i class="fas fa-network-wired w-6"></i> Phân Bổ Streamer
                </a>
            @endif

            <div class="pt-4 pb-1 text-xs text-slate-400 font-semibold px-3 uppercase tracking-wider">Khách Hàng</div>
            <a href="{{ route('talents.index') }}" target="_blank" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-800 text-slate-300 transition">
                <i class="fas fa-arrow-up-right-from-square w-6"></i> Xem Trang Portal
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800 bg-slate-950">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-slate-800 hover:bg-rose-900/40 text-slate-300 hover:text-rose-400 rounded-xl text-sm font-semibold transition">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                    <span>Đăng Xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Section -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-10">
            <h1 class="text-xl font-bold text-slate-800">@yield('page_title', 'Bảng Điều Khiển')</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-slate-500">MCN Agency Management v1.0</span>
            </div>
        </header>

        <!-- Scrollable Main Container -->
        <main class="flex-1 overflow-y-auto p-6 bg-slate-100">
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
                        <i class="fas fa-triangle-exclamation mr-2 text-amber-500"></i> Vui lòng sửa các lỗi sau:
                    </div>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

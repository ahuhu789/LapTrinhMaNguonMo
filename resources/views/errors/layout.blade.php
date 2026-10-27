{{--
    Standalone layout for error pages (403/404/419/429/500/503).
    Deliberately does not use layouts.app: error views may render without
    session, auth or shared view data.
--}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') - MCN Platform</title>
    @include('layouts.partials.fonts')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['"Be Vietnam Pro"', 'ui-sans-serif', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'Arial', '"Noto Sans"', 'sans-serif'] } } }
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center p-4 font-sans antialiased">
    <main class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl @yield('icon_bg', 'bg-violet-100 text-violet-600') flex items-center justify-center text-2xl mb-5">
            <i class="fas @yield('icon', 'fa-circle-info')" aria-hidden="true"></i>
        </div>
        <p class="text-xs font-bold tracking-widest @yield('code_color', 'text-violet-600') uppercase mb-1">Lỗi @yield('code')</p>
        <h1 class="text-xl font-extrabold text-slate-900 mb-2">@yield('heading')</h1>
        <p class="text-sm text-slate-500 leading-relaxed">@yield('message')</p>
        <div class="mt-6 flex flex-col sm:flex-row gap-2 justify-center">
            <a href="{{ url('/') }}" class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold shadow-sm transition">
                <i class="fas fa-house mr-1.5" aria-hidden="true"></i> Về trang chủ
            </a>
            @yield('extra_action')
            <a href="javascript:history.back()" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:border-violet-500 hover:text-violet-600 text-sm font-semibold transition">
                Quay lại
            </a>
        </div>
    </main>
</body>
</html>

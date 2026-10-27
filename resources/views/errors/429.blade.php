{{--
    429 Too Many Requests.
    Standalone page on purpose: error views can render without session/auth/shared
    view data, so it does not extend layouts.app (which needs $errors, Auth, routes...).
--}}
@php
    $retryAfter = null;
    if (isset($exception) && method_exists($exception, 'getHeaders')) {
        $headers = $exception->getHeaders();
        $raw = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
        if (is_numeric($raw)) {
            $retryAfter = max(1, (int) $raw);
        } elseif (is_string($raw) && ($ts = strtotime($raw)) !== false) {
            $retryAfter = max(1, $ts - time());
        }
    }
    $retryText = null;
    if ($retryAfter !== null) {
        $retryText = $retryAfter < 60 ? $retryAfter . ' giây' : (int) ceil($retryAfter / 60) . ' phút';
    }
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Quá nhiều yêu cầu (429) - MCN Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center p-4 font-sans antialiased">
    <main class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl mb-5">
            <i class="fas fa-hourglass-half" aria-hidden="true"></i>
        </div>
        <p class="text-xs font-bold tracking-widest text-amber-600 uppercase mb-1">Lỗi 429</p>
        <h1 class="text-xl font-extrabold text-slate-900 mb-2">Bạn thao tác quá nhanh</h1>
        <p class="text-sm text-slate-500 leading-relaxed">
            Hệ thống tạm thời giới hạn số lượng yêu cầu để bảo vệ dịch vụ.
            @if($retryText)
                Vui lòng thử lại sau <strong class="text-slate-800">{{ $retryText }}</strong>.
            @else
                Vui lòng đợi ít phút rồi thử lại.
            @endif
        </p>
        <div class="mt-6 flex flex-col sm:flex-row gap-2 justify-center">
            <a href="{{ url('/') }}" class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold shadow-sm transition">
                <i class="fas fa-house mr-1.5" aria-hidden="true"></i> Về trang chủ
            </a>
            <a href="javascript:history.back()" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:border-violet-500 hover:text-violet-600 text-sm font-semibold transition">
                Quay lại
            </a>
        </div>
    </main>
</body>
</html>

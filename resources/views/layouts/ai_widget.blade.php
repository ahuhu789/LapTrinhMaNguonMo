<!-- AI Chatbot Floating Trigger Button -->
<div id="ai-chat-widget" class="fixed bottom-6 right-6 z-50">
    <!-- Toggle Button -->
    <button id="ai-chat-toggle" class="bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-full px-4 py-3 shadow-xl flex items-center justify-center space-x-2 transition-all transform hover:scale-105 cursor-pointer border border-white/20">
        <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
        </span>
        <i class="fas fa-wand-magic-sparkles text-sm text-amber-300"></i>
        <span class="text-xs font-bold tracking-wide pr-1 hidden sm:inline">AI Cố Vấn Talent</span>
    </button>

    <!-- Chat Modal Window (Initially Hidden) -->
    <div id="ai-chat-window" class="hidden absolute bottom-16 right-0 w-[420px] sm:w-[450px] max-w-[92vw] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col h-[600px] max-h-[85vh] z-50 transition-all">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 p-4 text-white flex justify-between items-center select-none shadow-sm">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-sm shadow-inner">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <h4 class="font-bold text-xs">Trợ Lý AI Booking Talent</h4>
                        <span class="text-[9px] font-semibold uppercase px-1.5 py-0.5 rounded bg-emerald-400/20 text-emerald-200 border border-emerald-400/30">Gemini 3.5</span>
                    </div>
                    <p class="text-[10px] text-primary-100 flex items-center mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Sẵn sàng hỗ trợ theo CSDL Talent MCN
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-1">
                <button id="ai-chat-reset" class="text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition text-xs" title="Làm mới đoạn chat">
                    <i class="fas fa-rotate-right"></i>
                </button>
                <button id="ai-chat-close" class="text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition text-base" title="Đóng">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Chat Message Area -->
        <div id="ai-messages" class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs bg-slate-50">
            <!-- Bot Welcome Message -->
            <div class="flex items-start space-x-2.5 ai-message-animate">
                <div class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center text-xs shrink-0 mt-0.5 border border-primary-200 shadow-2xs">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div class="bg-white p-3.5 rounded-2xl rounded-tl-sm border border-slate-200 shadow-sm text-slate-700 max-w-[88%] text-[12px] leading-relaxed">
                    <div class="font-bold text-primary-600 text-xs mb-1.5 flex items-center space-x-1">
                        <i class="fas fa-sparkles"></i> <span>Xin chào bạn!</span>
                    </div>
                    Tôi là <strong>Trợ lý AI Cố vấn Talent</strong> của MCN Platform. Hãy cho tôi biết nhu cầu chiến dịch của bạn:
                    <ul class="mt-2 space-y-1 text-[11px] text-slate-600 list-disc list-inside">
                        <li>Tìm streamer theo <strong>ngân sách</strong> (ví dụ: dưới 2 triệu, 3 - 5 triệu/h)</li>
                        <li>Lựa chọn theo <strong>thể loại</strong> (Gaming CS2/LMHT, Review, Lifestyle...)</li>
                        <li>Gợi ý gương mặt tấu hài, triệu view hoặc tương tác cao</li>
                    </ul>
                    <div class="mt-2.5 text-[10px] text-slate-400 italic border-t border-slate-100 pt-2">
                        💡 Bạn có thể bấm chọn gợi ý bên dưới hoặc tự do chat nhé!
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick prompts chips -->
        <div class="px-3.5 py-2 bg-white border-t border-slate-200 flex space-x-2 overflow-x-auto no-scrollbar">
            <button type="button" class="quick-prompt whitespace-nowrap px-3 py-1.5 rounded-full bg-slate-100 hover:bg-primary-50 text-slate-700 hover:text-primary-700 hover:border-primary-300 border border-slate-200 transition text-[11px] font-medium cursor-pointer shrink-0 shadow-2xs flex items-center space-x-1.5">
                <span>🎮</span> <span>Streamer game 2-3 triệu</span>
            </button>
            <button type="button" class="quick-prompt whitespace-nowrap px-3 py-1.5 rounded-full bg-slate-100 hover:bg-primary-50 text-slate-700 hover:text-primary-700 hover:border-primary-300 border border-slate-200 transition text-[11px] font-medium cursor-pointer shrink-0 shadow-2xs flex items-center space-x-1.5">
                <span>📱</span> <span>Review đồ công nghệ</span>
            </button>
            <button type="button" class="quick-prompt whitespace-nowrap px-3 py-1.5 rounded-full bg-slate-100 hover:bg-primary-50 text-slate-700 hover:text-primary-700 hover:border-primary-300 border border-slate-200 transition text-[11px] font-medium cursor-pointer shrink-0 shadow-2xs flex items-center space-x-1.5">
                <span>😂</span> <span>Streamer tấu hài, duyên dáng</span>
            </button>
            <button type="button" class="quick-prompt whitespace-nowrap px-3 py-1.5 rounded-full bg-slate-100 hover:bg-primary-50 text-slate-700 hover:text-primary-700 hover:border-primary-300 border border-slate-200 transition text-[11px] font-medium cursor-pointer shrink-0 shadow-2xs flex items-center space-x-1.5">
                <span>👑</span> <span>Top streamer nổi tiếng nhất</span>
            </button>
        </div>

        <!-- Input Form -->
        <form id="ai-chat-form" class="p-3 bg-white border-t border-slate-200 flex flex-col space-y-1.5">
            <div class="flex items-center space-x-2">
                <input type="text" id="ai-chat-input" placeholder="Hỏi AI: 'Tìm streamer CS2 tầm 2.5 triệu'..." class="flex-1 text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-500 transition text-slate-800 placeholder-slate-400" required autocomplete="off">
                <button type="submit" id="ai-chat-submit" class="bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl px-4 py-2.5 text-xs font-semibold shadow-sm transition flex items-center justify-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
            <div class="flex justify-between items-center text-[10px] text-slate-400 px-1">
                <span>⚡ Powered by Google Gemini AI</span>
                <span>Dữ liệu CSDL MCN Real-time</span>
            </div>
        </form>
    </div>
</div>

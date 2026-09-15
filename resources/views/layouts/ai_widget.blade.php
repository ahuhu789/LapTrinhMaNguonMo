<!-- AI Chatbot Floating Trigger Button -->
<div id="ai-chat-widget" class="fixed bottom-6 right-6 z-50">
    <!-- Toggle Button -->
    <button id="ai-chat-toggle" class="bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-full p-4 shadow-xl flex items-center justify-center space-x-2 transition-all transform hover:scale-105">
        <i class="fas fa-wand-magic-sparkles text-xl"></i>
        <span class="text-sm font-bold pr-1 hidden sm:inline">AI Cố Vấn Talent</span>
    </button>

    <!-- Chat Modal Window (Initially Hidden) -->
    <div id="ai-chat-window" class="hidden absolute bottom-16 right-0 w-96 max-w-[90vw] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col h-[520px]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 p-4 text-white flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-robot text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm">Trợ Lý AI Booking Talent</h4>
                    <p class="text-xs text-primary-100 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 mr-1 animate-pulse"></span> Sẵn sàng hỗ trợ bạn
                    </p>
                </div>
            </div>
            <button id="ai-chat-close" class="text-white/80 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Chat Message Area -->
        <div id="ai-messages" class="flex-1 p-4 overflow-y-auto space-y-3 text-sm bg-slate-50">
            <!-- Bot Welcome Message -->
            <div class="flex items-start space-x-2">
                <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-tl-none border border-slate-200 shadow-sm text-slate-700">
                    Xin chào! Tôi là AI Cố Vấn của MCN Platform. Bạn đang tìm Streamer thuộc lĩnh vực nào, hay có ngân sách bao nhiêu? Hãy nhắn cho tôi nhé!
                </div>
            </div>
        </div>

        <!-- Quick prompts -->
        <div class="px-3 py-2 bg-slate-100 border-t border-slate-200 flex flex-wrap gap-1.5 text-xs text-slate-600">
            <button class="quick-prompt bg-white px-2 py-1 rounded-md border border-slate-200 hover:border-primary-500 transition">
                Tìm streamer game dưới 1.5 triệu
            </button>
            <button class="quick-prompt bg-white px-2 py-1 rounded-md border border-slate-200 hover:border-primary-500 transition">
                Cần streamer review đồ công nghệ
            </button>
        </div>

        <!-- Input Form -->
        <form id="ai-chat-form" class="p-3 bg-white border-t border-slate-200 flex items-center space-x-2">
            <input type="text" id="ai-chat-input" placeholder="Nhập tiêu chí (ngân sách, chủ đề...)" class="flex-1 text-sm border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="off">
            <button type="submit" id="ai-chat-submit" class="bg-primary-600 hover:bg-primary-700 text-white rounded-xl px-4 py-2 text-sm font-semibold transition">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

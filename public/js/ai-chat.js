// AI Talent Recommendation Chatbot Widget
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('ai-chat-toggle');
    const closeBtn = document.getElementById('ai-chat-close');
    const chatWindow = document.getElementById('ai-chat-window');
    const chatForm = document.getElementById('ai-chat-form');
    const chatInput = document.getElementById('ai-chat-input');
    const messagesContainer = document.getElementById('ai-messages');
    const quickPrompts = document.querySelectorAll('.quick-prompt');

    if (!toggleBtn || !chatWindow) return;

    // Toggle chat window
    toggleBtn.addEventListener('click', function () {
        chatWindow.classList.toggle('hidden');
        if (!chatWindow.classList.contains('hidden')) {
            chatInput.focus();
        }
    });

    closeBtn.addEventListener('click', function () {
        chatWindow.classList.add('hidden');
    });

    // Quick prompt buttons
    quickPrompts.forEach(btn => {
        btn.addEventListener('click', function () {
            chatInput.value = this.innerText.trim();
            chatForm.dispatchEvent(new Event('submit'));
        });
    });

    // Chat form submit
    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        // 1. Add User Message
        appendMessage('user', text);
        chatInput.value = '';

        // 2. Add Loading Indicator
        const loadingId = appendLoading();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch('/ai/recommend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ message: text })
            });

            removeLoading(loadingId);

            if (!response.ok) {
                throw new Error('Lỗi kết nối máy chủ');
            }

            const data = await response.json();
            if (data.success) {
                appendMessage('bot', data.reply, data.matched_talents);
            } else {
                appendMessage('bot', 'Xin lỗi, tôi chưa hiểu rõ yêu cầu. Bạn có thể nêu cụ thể thể loại game hoặc ngân sách không?');
            }
        } catch (error) {
            removeLoading(loadingId);
            appendMessage('bot', 'Không thể kết nối đến máy chủ AI. Vui lòng kiểm tra lại đường truyền mạng.');
            console.error(error);
        }
    });

    function appendMessage(sender, text, talents = []) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'ai-message-animate flex items-start space-x-2 ' + (sender === 'user' ? 'justify-end' : '');

        if (sender === 'user') {
            msgDiv.innerHTML = `
                <div class="bg-primary-600 text-white p-3 rounded-2xl rounded-tr-none shadow-sm max-w-[85%] text-xs">
                    ${escapeHtml(text)}
                </div>
            `;
        } else {
            // Format markdown-like bold text
            let formattedText = escapeHtml(text)
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');

            let talentsHtml = '';
            if (talents && talents.length > 0) {
                talentsHtml = `
                    <div class="mt-3 space-y-2 border-t border-slate-200 pt-2">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Gợi Ý Talent Phù Hợp:</div>
                        ${talents.map(t => `
                            <a href="/talents/${t.id}" target="_blank" class="block p-2 rounded-xl bg-slate-50 hover:bg-primary-50 border border-slate-200 transition">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-slate-800 text-xs">${t.stage_name}</span>
                                    <span class="text-[11px] font-extrabold text-primary-600">${new Intl.NumberFormat('vi-VN').format(t.rate_per_hour)} đ/h</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    ${t.category} • ~${new Intl.NumberFormat('vi-VN').format(t.avg_viewers)} viewers
                                </div>
                            </a>
                        `).join('')}
                    </div>
                `;
            }

            msgDiv.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-tl-none border border-slate-200 shadow-sm text-slate-700 max-w-[85%] text-xs leading-relaxed">
                    <div>${formattedText}</div>
                    ${talentsHtml}
                </div>
            `;
        }

        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function appendLoading() {
        const id = 'loading-' + Date.now();
        const loadingDiv = document.createElement('div');
        loadingDiv.id = id;
        loadingDiv.className = 'flex items-start space-x-2 ai-message-animate';
        loadingDiv.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                <i class="fas fa-robot"></i>
            </div>
            <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none border border-slate-200 shadow-sm text-slate-500 text-xs flex items-center space-x-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-bounce"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-bounce [animation-delay:0.4s]"></span>
                <span class="text-[11px] text-slate-400 ml-1">AI đang lọc Talent...</span>
            </div>
        `;
        messagesContainer.appendChild(loadingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return id;
    }

    function removeLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function escapeHtml(string) {
        const entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        };
        return String(string).replace(/[&<>"']/g, function (s) {
            return entityMap[s];
        });
    }
});

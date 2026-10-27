// AI Talent Recommendation Chatbot Widget (Google Gemini 3.5 Integration)
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('ai-chat-toggle');
    const closeBtn = document.getElementById('ai-chat-close');
    const resetBtn = document.getElementById('ai-chat-reset');
    const chatWindow = document.getElementById('ai-chat-window');
    const chatForm = document.getElementById('ai-chat-form');
    const chatInput = document.getElementById('ai-chat-input');
    const chatSubmit = document.getElementById('ai-chat-submit');
    const messagesContainer = document.getElementById('ai-messages');
    const quickPrompts = document.querySelectorAll('.quick-prompt');

    if (!toggleBtn || !chatWindow) return;

    const DEFAULT_AVATAR = 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=200';

    // Cache initial welcome message HTML for reset
    const initialWelcomeHtml = messagesContainer.innerHTML;

    // Toggle chat window open / close
    toggleBtn.addEventListener('click', function () {
        chatWindow.classList.toggle('hidden');
        if (!chatWindow.classList.contains('hidden')) {
            chatInput.focus();
            scrollToBottom();
        }
    });

    closeBtn?.addEventListener('click', function () {
        chatWindow.classList.add('hidden');
    });

    // Reset conversation
    resetBtn?.addEventListener('click', function () {
        messagesContainer.innerHTML = initialWelcomeHtml;
        chatInput.value = '';
        chatInput.focus();
    });

    // Quick prompt buttons
    quickPrompts.forEach(btn => {
        btn.addEventListener('click', function () {
            // Strip leading emoji if present
            const text = this.innerText.replace(/^[^\w\sÀ-ỹ]+/, '').trim();
            chatInput.value = text;
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
        setSubmitting(true);

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
                appendMessage('bot', data.reply, data.matched_talents || []);
            } else {
                appendMessage('bot', 'Xin lỗi, tôi chưa hiểu rõ yêu cầu. Bạn có thể nêu cụ thể thể loại game (CS2, LMHT...) hoặc ngân sách không?');
            }
        } catch (error) {
            removeLoading(loadingId);
            appendMessage('bot', 'Không thể kết nối đến máy chủ AI lúc này. Vui lòng thử lại sau ít giây.');
            console.error(error);
        } finally {
            setSubmitting(false);
            chatInput.focus();
        }
    });

    function setSubmitting(isSubmitting) {
        if (chatSubmit) chatSubmit.disabled = isSubmitting;
        if (chatInput) chatInput.disabled = isSubmitting;
    }

    function appendMessage(sender, text, talents = []) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'ai-message-animate flex items-start space-x-2.5 ' + (sender === 'user' ? 'justify-end' : '');

        if (sender === 'user') {
            msgDiv.innerHTML = `
                <div class="bg-primary-600 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm max-w-[85%] text-xs leading-relaxed font-normal">
                    ${escapeHtml(text)}
                </div>
                <div class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-[10px] shrink-0 mt-0.5 font-bold">
                    <i class="fas fa-user"></i>
                </div>
            `;
        } else {
            const formattedContent = renderMarkdown(text);
            const talentsHtml = renderTalentsCards(talents);

            msgDiv.innerHTML = `
                <div class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center text-xs shrink-0 mt-0.5 border border-primary-200 shadow-2xs">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div class="bg-white p-3.5 rounded-2xl rounded-tl-sm border border-slate-200 shadow-sm text-slate-700 max-w-[88%] text-[12px] leading-relaxed">
                    <div class="ai-formatted-text space-y-1.5">${formattedContent}</div>
                    ${talentsHtml}
                </div>
            `;
        }

        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
    }

    function renderMarkdown(rawText) {
        if (!rawText) return '';

        const lines = rawText.split('\n');
        const outputLines = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();

            if (!line) {
                outputLines.push('<div class="h-1"></div>');
                continue;
            }

            // Horizontal rule
            if (/^---+$/.test(line)) {
                outputLines.push('<hr class="my-2 border-slate-200">');
                continue;
            }

            // Headers: ### or ##
            if (/^###\s+(.*)/.test(line)) {
                const title = line.replace(/^###\s+/, '');
                outputLines.push(`<div class="font-bold text-primary-600 text-[12px] mt-2 mb-0.5">${formatInlineMarkdown(title)}</div>`);
                continue;
            }
            if (/^##\s+(.*)/.test(line)) {
                const title = line.replace(/^##\s+/, '');
                outputLines.push(`<div class="font-extrabold text-slate-800 text-[13px] mt-2 mb-1">${formatInlineMarkdown(title)}</div>`);
                continue;
            }

            // Numbered list item: 1. Item
            const numMatch = line.match(/^(\d+)\.\s+(.*)/);
            if (numMatch) {
                const num = numMatch[1];
                const content = formatInlineMarkdown(numMatch[2]);
                outputLines.push(`
                    <div class="flex items-start space-x-1.5 mt-1.5 mb-0.5">
                        <span class="w-4 h-4 rounded-full bg-primary-100 text-primary-700 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5 border border-primary-200">${num}</span>
                        <div class="flex-1 text-slate-800">${content}</div>
                    </div>
                `);
                continue;
            }

            // Bullet list item: * Item or - Item
            const bulletMatch = line.match(/^[\*\-]\s+(.*)/);
            if (bulletMatch) {
                const content = formatInlineMarkdown(bulletMatch[1]);
                outputLines.push(`
                    <div class="flex items-start space-x-2 pl-2 my-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0"></span>
                        <div class="flex-1 text-slate-700">${content}</div>
                    </div>
                `);
                continue;
            }

            // Regular line
            outputLines.push(`<div>${formatInlineMarkdown(line)}</div>`);
        }

        return outputLines.join('');
    }

    function formatInlineMarkdown(str) {
        let text = escapeHtml(str);

        // Bold: **text**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900">$1</strong>');

        // Italic: *text* (avoiding double asterisks already matched)
        text = text.replace(/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/g, '<em class="italic text-primary-700 font-medium">$1</em>');

        // Inline code
        text = text.replace(/`([^`]+)`/g, '<code class="bg-slate-100 text-primary-600 px-1 py-0.5 rounded text-[10px] font-mono">$1</code>');

        return text;
    }

    function renderTalentsCards(talents) {
        if (!talents || talents.length === 0) return '';

        return `
            <div class="mt-3.5 pt-3 border-t border-slate-200 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-bold text-primary-600 uppercase tracking-wider mb-1.5">
                    <span class="flex items-center space-x-1.5">
                        <i class="fas fa-sparkles text-xs"></i>
                        <span>Streamer Phù Hợp:</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-normal lowercase">${talents.length} hồ sơ</span>
                </div>
                ${talents.map(t => {
                    const avatar = safeImageUrl(t.avatar_url);
                    const formattedRate = new Intl.NumberFormat('vi-VN').format(t.rate_per_hour) + ' đ/h';
                    const formattedViews = new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(Number(t.avg_viewers_calc ?? t.avg_viewers ?? 0) || 0);
                    const bioSnippet = t.bio ? escapeHtml(t.bio) : '';

                    return `
                        <div class="p-2.5 rounded-xl bg-slate-50 hover:bg-white border border-slate-200 hover:border-primary-300 hover:shadow-md transition-all duration-150 group">
                            <div class="flex items-center space-x-2.5">
                                <img src="${escapeHtml(avatar)}" alt="${escapeHtml(t.stage_name)}" width="44" height="44" loading="lazy" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shadow-2xs shrink-0 group-hover:scale-105 transition-transform" onerror="this.onerror=null;this.src='${DEFAULT_AVATAR}'">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold text-slate-800 text-xs truncate flex items-center space-x-1">
                                            <span>${escapeHtml(t.stage_name)}</span>
                                            <i class="fas fa-circle-check text-blue-500 text-[10px]" title="Verified Talent"></i>
                                        </span>
                                        <span class="text-[11px] font-extrabold text-primary-600 shrink-0">${formattedRate}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-0.5 text-[10px] text-slate-500">
                                        <span class="px-1.5 py-0.2 rounded bg-white border border-slate-200 font-medium text-slate-700">${escapeHtml(t.category)}</span>
                                        <span><i class="fas fa-eye text-slate-400 mr-0.5"></i> ~${formattedViews} viewers</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="text-[10px] text-slate-400 line-clamp-1 flex-1 pr-2">${bioSnippet}</span>
                                <a href="/talents/${encodeURIComponent(t.id)}" target="_blank" class="shrink-0 text-[10px] font-bold text-primary-600 hover:text-primary-700 inline-flex items-center space-x-1 group-hover:translate-x-0.5 transition-transform bg-primary-50 hover:bg-primary-100 px-2 py-0.5 rounded">
                                    <span>Hồ Sơ</span>
                                    <i class="fas fa-arrow-right text-[8px]"></i>
                                </a>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function appendLoading() {
        const id = 'loading-' + Date.now();
        const loadingDiv = document.createElement('div');
        loadingDiv.id = id;
        loadingDiv.className = 'flex items-start space-x-2.5 ai-message-animate';
        loadingDiv.innerHTML = `
            <div class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center text-xs shrink-0 mt-0.5 border border-primary-200 shadow-2xs animate-pulse">
                <i class="fas fa-wand-magic-sparkles"></i>
            </div>
            <div class="bg-white px-3.5 py-3 rounded-2xl rounded-tl-sm border border-slate-200 shadow-sm text-slate-500 text-xs flex items-center space-x-2">
                <div class="flex items-center space-x-1">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-bounce"></span>
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-bounce [animation-delay:0.15s]"></span>
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-bounce [animation-delay:0.3s]"></span>
                </div>
                <span class="text-[11px] text-slate-400">Gemini đang tra cứu CSDL Talent...</span>
            </div>
        `;
        messagesContainer.appendChild(loadingDiv);
        scrollToBottom();
        return id;
    }

    function removeLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 50);
    }

    // Only allow http(s) or same-origin relative URLs for images; anything else
    // (javascript:, data:, protocol-relative //host, malformed) falls back to the default avatar.
    function safeImageUrl(url) {
        if (typeof url !== 'string') return DEFAULT_AVATAR;
        const trimmed = url.trim();
        if (!trimmed) return DEFAULT_AVATAR;
        // Relative path on the same origin (e.g. /images/streamers/x.jpg) but not //evil.host
        if (trimmed.startsWith('/') && !trimmed.startsWith('//')) return trimmed;
        try {
            const parsed = new URL(trimmed, window.location.origin);
            if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
                return parsed.href;
            }
        } catch (e) {
            // invalid URL -> fall through to default
        }
        return DEFAULT_AVATAR;
    }

    function escapeHtml(string) {
        if (!string) return '';
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

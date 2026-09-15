<?php

namespace App\Services;

use App\Models\StreamerProfile;
use Illuminate\Support\Facades\Http;
use Exception;

class AiRecommendationService
{
    /**
     * Gợi ý Talent Streamers dựa trên tiêu chí của khách hàng
     *
     * @param string $userQuery Câu hỏi / yêu cầu từ phía Client
     * @return array Kết quả gợi ý dạng mảng (câu trả lời, danh sách talent phù hợp)
     */
    public function recommendTalents(string $userQuery): array
    {
        // 1. Data Context Injection: Truy vấn danh sách Streamer hoạt động trong CSDL
        $talents = StreamerProfile::where('status', 'active')
            ->with(['metrics'])
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'stage_name' => $t->stage_name,
                    'category' => $t->category,
                    'rate_per_hour' => (float) $t->rate_per_hour,
                    'avg_viewers' => $t->avg_viewers,
                    'peak_viewers' => $t->peak_viewers,
                    'bio' => $t->bio,
                    'profile_url' => url('/talents/' . $t->id),
                ];
            })->toArray();

        // 2. Thử gọi API LLM (Gemini hoặc OpenAI) nếu có API Key
        $geminiKey = config('services.ai.gemini_key') ?: env('GEMINI_API_KEY');
        $openAiKey = config('services.ai.openai_key') ?: env('OPENAI_API_KEY');

        if (!empty($geminiKey)) {
            $aiResponse = $this->callGeminiApi($userQuery, $talents, $geminiKey);
            if ($aiResponse['success']) {
                return $aiResponse;
            }
        }

        if (!empty($openAiKey)) {
            $aiResponse = $this->callOpenAiApi($userQuery, $talents, $openAiKey);
            if ($aiResponse['success']) {
                return $aiResponse;
            }
        }

        // 3. Fallback: Kích hoạt Thuật toán Rule-Based Search thông minh
        return $this->fallbackRuleBasedRecommendation($userQuery, $talents);
    }

    /**
     * Gọi Google Gemini REST API với Context Injection
     */
    protected function callGeminiApi(string $query, array $talents, string $apiKey): array
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

            $systemInstruction = "Bạn là Trợ lý Cố vấn Booking Talent của MCN Platform. Nhiệm vụ của bạn là dựa vào CSDL Talent Streamers được cung cấp bên dưới để tư vấn và gợi ý 1 đến 3 streamer phù hợp nhất với yêu cầu, ngân sách và thể loại của nhãn hàng. Trả lời bằng tiếng Việt, súc tích, thân thiện, nêu rõ lý do gợi ý và mức giá tham khảo.";

            $prompt = $systemInstruction . "\n\n" .
                "DANH SÁCH TALENT TRONG HỆ THỐNG:\n" . json_encode($talents, JSON_UNESCAPED_UNICODE) . "\n\n" .
                "YÊU CẦU TỪ KHÁCH HÀNG:\n" . $query;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 800,
                ]
            ];

            $response = Http::timeout(6)->withHeaders([
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($reply)) {
                    return [
                        'success' => true,
                        'source' => 'gemini_ai',
                        'reply' => $reply,
                        'matched_talents' => $this->extractRelevantTalents($query, $talents),
                    ];
                }
            }
        } catch (Exception $e) {
            // Lỗi mạng hoặc quá thời gian -> Chuyển sang fallback
        }

        return ['success' => false];
    }

    /**
     * Gọi OpenAI API
     */
    protected function callOpenAiApi(string $query, array $talents, string $apiKey): array
    {
        try {
            $response = Http::timeout(6)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Bạn là Trợ lý Tư vấn Booking Talent của MCN Platform. Hãy dựa vào danh sách talent sau đây để trả lời câu hỏi của khách hàng: " . json_encode($talents, JSON_UNESCAPED_UNICODE)
                    ],
                    ['role' => 'user', 'content' => $query]
                ],
                'temperature' => 0.4,
                'max_tokens' => 600,
            ]);

            if ($response->successful()) {
                $reply = $response->json('choices.0.message.content');
                if (!empty($reply)) {
                    return [
                        'success' => true,
                        'source' => 'openai',
                        'reply' => $reply,
                        'matched_talents' => $this->extractRelevantTalents($query, $talents),
                    ];
                }
            }
        } catch (Exception $e) {
            // Lỗi mạng hoặc key hết hạn -> Chuyển sang fallback
        }

        return ['success' => false];
    }

    /**
     * Fallback Engine: Khớp từ khóa và chấm điểm độ phù hợp (Rule-based Scoring)
     */
    public function fallbackRuleBasedRecommendation(string $query, array $talents): array
    {
        $queryLower = mb_strtolower($query);

        // 1. Phân tích ngân sách tối đa trong câu truy vấn (ví dụ: 'dưới 2 triệu', '1.5tr', '800k')
        $maxBudget = null;
        if (preg_match('/(\d+(?:[\.,]\d+)?)\s*(triệu|tr|m)\b/u', $queryLower, $matches)) {
            $val = (float) str_replace(',', '.', $matches[1]);
            $maxBudget = $val * 1000000;
        } elseif (preg_match('/(\d+)\s*(k|nghìn|ngàn)\b/u', $queryLower, $matches)) {
            $maxBudget = ((float) $matches[1]) * 1000;
        }

        // 2. Phân tích thể loại
        $targetCategory = null;
        if (str_contains($queryLower, 'game') || str_contains($queryLower, 'cs2') || str_contains($queryLower, 'valorant') || str_contains($queryLower, 'liên quân') || str_contains($queryLower, 'esport')) {
            $targetCategory = 'Gaming';
        } elseif (str_contains($queryLower, 'chat') || str_contains($queryLower, 'tâm sự') || str_contains($queryLower, 'hát') || str_contains($queryLower, 'giải trí') || str_contains($queryLower, 'asmr')) {
            $targetCategory = 'Just Chatting';
        } elseif (str_contains($queryLower, 'review') || str_contains($queryLower, 'công nghệ') || str_contains($queryLower, 'tech') || str_contains($queryLower, 'unboxing') || str_contains($queryLower, 'đánh giá')) {
            $targetCategory = 'Review';
        } elseif (str_contains($queryLower, 'lifestyle') || str_contains($queryLower, 'làm đẹp') || str_contains($queryLower, 'makeup') || str_contains($queryLower, 'thời trang') || str_contains($queryLower, 'du lịch')) {
            $targetCategory = 'Lifestyle';
        }

        // 3. Chấm điểm từng streamer
        $scored = [];
        foreach ($talents as $talent) {
            $score = 0;

            // Thể loại khớp
            if ($targetCategory && $talent['category'] === $targetCategory) {
                $score += 50;
            }

            // Ngân sách phù hợp
            if ($maxBudget) {
                if ($talent['rate_per_hour'] <= $maxBudget) {
                    $score += 40;
                } else {
                    $score -= 30; // Vượt ngân sách
                }
            } else {
                $score += 10;
            }

            // Khớp từ khóa trong bio hoặc tên
            $words = explode(' ', $queryLower);
            foreach ($words as $word) {
                if (mb_strlen($word) >= 3) {
                    if (str_contains(mb_strtolower($talent['stage_name']), $word)) $score += 15;
                    if (str_contains(mb_strtolower($talent['bio'] ?? ''), $word)) $score += 10;
                }
            }

            // Điểm cộng theo mức độ nổi tiếng (avg_viewers)
            $score += min(20, (int) round($talent['avg_viewers'] / 1000));

            $scored[] = [
                'talent' => $talent,
                'score' => $score,
            ];
        }

        // Sắp xếp điểm giảm dần
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        $topTalents = array_slice(array_map(fn($item) => $item['talent'], $scored), 0, 3);

        // Tạo câu trả lời tự động bằng tiếng Việt
        $replyLines = [];
        $replyLines[] = "Dựa trên hệ sinh thái Talent của MCN Platform, chúng tôi đã phân tích và chọn lọc các Streamer phù hợp nhất với yêu cầu của bạn:";

        foreach ($topTalents as $idx => $t) {
            $formattedRate = number_format($t['rate_per_hour'], 0, ',', '.') . ' đ/giờ';
            $formattedView = number_format($t['avg_viewers'], 0, ',', '.') . ' viewers';
            $replyLines[] = sprintf(
                "**%d. %s** (Thể loại: %s)\n- Mức giá: %s | Tương tác TB: %s\n- Điểm nổi bật: %s",
                $idx + 1,
                $t['stage_name'],
                $t['category'],
                $formattedRate,
                $formattedView,
                $t['bio']
            );
        }

        $replyLines[] = "\nBạn có thể click trực tiếp vào danh thiếp bên dưới để xem **Talent Media Kit** đầy đủ và gửi yêu cầu booking ngay lập tức!";

        return [
            'success' => true,
            'source' => 'rule_based_fallback',
            'reply' => implode("\n\n", $replyLines),
            'matched_talents' => $topTalents,
        ];
    }

    /**
     * Trích xuất danh sách Streamer khớp để hiển thị danh thiếp gợi ý
     */
    protected function extractRelevantTalents(string $query, array $talents): array
    {
        $fallback = $this->fallbackRuleBasedRecommendation($query, $talents);
        return $fallback['matched_talents'] ?? array_slice($talents, 0, 3);
    }
}

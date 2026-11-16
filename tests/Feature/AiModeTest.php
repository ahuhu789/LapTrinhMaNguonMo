<?php

namespace Tests\Feature;

use App\Models\StreamerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StreamerProfile::factory()->count(2)->create();
    }

    public function test_json_reports_gemini_when_gemini_answers(): void
    {
        config(['services.ai.gemini_key' => 'k', 'services.ai.openai_key' => '', 'services.ai.gemini_models' => ['m1']]);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Gợi ý']]]]]])]);

        $this->postJson(route('ai.recommend'), ['message' => 'streamer gaming'])
            ->assertOk()
            ->assertJsonPath('ai_mode', 'gemini')
            ->assertJsonPath('source', 'gemini_ai');
    }

    public function test_json_reports_fallback_when_gemini_fails(): void
    {
        config(['services.ai.gemini_key' => 'k', 'services.ai.openai_key' => '', 'services.ai.gemini_models' => ['m1']]);
        Http::fake(['*' => Http::response('quota', 429)]);

        $this->postJson(route('ai.recommend'), ['message' => 'streamer gaming'])
            ->assertOk()
            ->assertJsonPath('ai_mode', 'fallback');
    }

    public function test_json_reports_fallback_without_key(): void
    {
        config(['services.ai.gemini_key' => '', 'services.ai.openai_key' => '']);
        Http::fake();

        $this->postJson(route('ai.recommend'), ['message' => 'streamer gaming'])
            ->assertOk()
            ->assertJsonPath('ai_mode', 'fallback');

        Http::assertNothingSent();
    }

    public function test_views_receive_configured_ai_mode(): void
    {
        config(['services.ai.gemini_key' => '']);
        $this->assertSame('fallback', $this->get(route('home'))->assertOk()->viewData('aiMode'));

        config(['services.ai.gemini_key' => 'k']);
        $this->assertSame('gemini', $this->get(route('home'))->assertOk()->viewData('aiMode'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\StreamMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MetricsUploadTest extends TestCase
{
    use RefreshDatabase;

    private const HEADER = 'streamer_id,platform,stream_date,duration_hours,avg_viewers,peak_viewers,followers_gained';

    private function csv(string $content, string $name = 'metrics.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_manager_upload_imports_only_own_streamers_and_flashes_row_errors_once(): void
    {
        $manager = $this->makeUser('manager');
        $own = $this->makeStreamer($manager);
        $foreign = $this->makeStreamer($this->makeUser('manager'));

        $file = $this->csv(implode("\n", [
            self::HEADER,
            "{$own->id},youtube,2026-10-01,3,1000,2000,10",
            "{$foreign->id},youtube,2026-10-01,3,1000,2000,10",
        ]));

        $this->actingAs($manager)
            ->from(route('manager.metrics.upload'))
            ->post(route('manager.metrics.store'), ['csv_file' => $file])
            ->assertRedirect(route('manager.metrics.upload'))
            ->assertSessionHas('success')
            ->assertSessionMissing('error')
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 1
                && str_contains($errors[0], "Streamer ID ({$foreign->id})"));

        $this->assertSame([$own->id], StreamMetric::pluck('streamer_id')->all());
    }

    public function test_admin_upload_can_import_any_streamer_without_row_errors_flash(): void
    {
        $streamer = $this->makeStreamer($this->makeUser('manager'));

        $this->actingAs($this->makeUser('admin'))
            ->post(route('manager.metrics.store'), [
                'csv_file' => $this->csv(self::HEADER . "\n{$streamer->id},tiktok,2026-10-02,2,500,900,1"),
            ])
            ->assertSessionHas('success')
            ->assertSessionMissing('import_errors');

        $this->assertSame(1, StreamMetric::count());
    }

    public function test_file_level_error_flashes_only_error(): void
    {
        $this->actingAs($this->makeUser('manager'))
            ->post(route('manager.metrics.store'), [
                'csv_file' => $this->csv("streamer_id,platform\n1,youtube"),
            ])
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Cột thiếu: stream_date'))
            ->assertSessionMissing('import_errors')
            ->assertSessionMissing('success');
    }

    public function test_invalid_extension_is_rejected_with_vietnamese_message(): void
    {
        $this->actingAs($this->makeUser('manager'))
            ->post(route('manager.metrics.store'), [
                'csv_file' => UploadedFile::fake()->create('metrics.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['csv_file' => 'Chỉ chấp nhận tệp tin có định dạng .csv hoặc .txt.']);
    }

    public function test_streamer_and_client_cannot_upload(): void
    {
        foreach (['streamer', 'client'] as $role) {
            $this->actingAs($this->makeUser($role))
                ->post(route('manager.metrics.store'), ['csv_file' => $this->csv(self::HEADER)])
                ->assertForbidden();
        }
    }
}

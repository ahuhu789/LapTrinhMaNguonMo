<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tài liệu (README, .env.example) phải khớp với code: SQLite, tài khoản demo có thật trong seeder.
 */
class ProjectDocsTest extends TestCase
{
    use RefreshDatabase;

    private function envExample(): array
    {
        $values = [];
        foreach (file(base_path('.env.example'), FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^([A-Z0-9_]+)=(.*)$/', trim($line), $m)) {
                $values[$m[1]] = $m[2];
            }
        }

        return $values;
    }

    public function test_env_example_and_default_connection_use_sqlite(): void
    {
        $env = $this->envExample();

        $this->assertSame('sqlite', $env['DB_CONNECTION'] ?? null);
        $this->assertArrayNotHasKey('DB_HOST', $env, 'Các biến MySQL phải để dạng comment');
        $this->assertSame('Asia/Ho_Chi_Minh', $env['APP_TIMEZONE'] ?? null);

        $config = require base_path('config/database.php');
        $this->assertArrayHasKey('sqlite', $config['connections']);
        $this->assertStringContainsString("env('DB_CONNECTION', 'sqlite')", file_get_contents(base_path('config/database.php')));
    }

    public function test_readme_mentions_course_sqlite_and_docker(): void
    {
        $readme = file_get_contents(base_path('README.md'));

        $this->assertStringContainsString('Lập trình mã nguồn mở', $readme);
        $this->assertStringContainsString('touch database/database.sqlite', $readme);
        $this->assertStringContainsString('docker build', $readme);
        $this->assertStringContainsString('Render', $readme);
    }

    public function test_every_demo_account_in_readme_exists_in_seeder_with_documented_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $readme = file_get_contents(base_path('README.md'));

        preg_match_all('/`([a-z0-9]+@mcn\.com)`/', $readme, $m);
        $emails = array_unique($m[1]);
        $this->assertNotEmpty($emails);
        $this->assertStringContainsString('`password123`', $readme);

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "README nhắc tới $email nhưng seeder không tạo");
            $this->assertTrue(Hash::check('password123', $user->password), $email);
        }

        foreach (['admin', 'manager', 'streamer', 'client'] as $role) {
            $this->assertTrue(
                User::where('role', $role)->whereIn('email', $emails)->exists(),
                "README thiếu tài khoản demo vai trò $role"
            );
        }
    }
}

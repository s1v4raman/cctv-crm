<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Worker;
use Database\Seeders\DemoAnalyticsSeeder;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Opens every GET page as each role on the demo data and fails on any server error (500).
 * A new page that crashes for one role shows up here with the role, the URL and the cause.
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    // Files, exports and API endpoints are covered by their own tests
    private const SKIP = '#^(api/|_ignition|sanctum|up$|storage|webhook|auth/google|logout|mobile-scanner|docs/|.*(pdf|export|download|csv|print|receipt|signature-sheet|photo|vcard|send-whatsapp).*)#i';

    public function test_every_page_opens_without_a_server_error_for_every_role(): void
    {
        $this->seed();
        $this->seed(DemoAnalyticsSeeder::class);

        $worker = Worker::create(['name' => 'Smoke Worker', 'phone' => '9000099911', 'daily_rate' => 750, 'is_active' => true]);
        $workerUser = User::factory()->create(['role' => 'worker', 'email' => 'worker_9000099911@cctv-crm.local']);
        $worker->update(['user_id' => $workerUser->id]);

        $users = [
            'admin' => User::where('email', 'test@example.com')->first(),
            'staff' => User::where('email', 'alex@example.com')->first(),
            'technician' => User::where('email', 'bob@example.com')->first(),
            'worker' => $workerUser,
        ];

        $errors = [];
        $opened = 0;
        foreach ($users as $role => $user) {
            $this->assertNotNull($user, "Seeded {$role} user is missing");

            foreach (Route::getRoutes() as $route) {
                $uri = $this->pageUri($route);
                if ($uri === null) continue;

                app('auth')->forgetGuards();
                $response = $this->actingAs($user)->get($uri);
                $opened++;

                if ($response->getStatusCode() >= 500) {
                    $e = $response->exception;
                    $origin = $e ? ($e->getPrevious() ?? $e) : null;
                    $errors[] = sprintf('%s %s -> %s%s', $role, $uri,
                        $e ? class_basename($e) . ': ' . mb_substr(preg_replace('/\s+/', ' ', $e->getMessage()), 0, 160) : 'HTTP ' . $response->getStatusCode(),
                        $origin ? ' [' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $origin->getFile()) . ':' . $origin->getLine() . ']' : '');
                }
            }
        }

        $this->assertGreaterThan(200, $opened, 'The crawl opened too few pages; check the route filter.');
        $this->assertSame([], $errors, "Pages with server errors:\n" . implode("\n", $errors));
    }

    /**
     * A concrete URL for a GET page route, filling route parameters with the first record of
     * each bound model, or null when the route is skipped or has no record to show.
     */
    private function pageUri(RouteDefinition $route): ?string
    {
        if (!in_array('GET', $route->methods(), true) || preg_match(self::SKIP, $route->uri())) {
            return null;
        }

        $params = [];
        foreach ($route->signatureParameters(['subClass' => UrlRoutable::class]) as $parameter) {
            $class = $parameter->getType()?->getName();
            $model = $class ? $class::query()->first() : null;
            if (!$model) return null;
            $params[$parameter->getName()] = $model->getRouteKey();
        }

        preg_match_all('/\{(\w+)(\?)?\}/', $route->uri(), $placeholders, PREG_SET_ORDER);
        foreach ($placeholders as $placeholder) {
            $optional = ($placeholder[2] ?? '') === '?';
            if (!isset($params[$placeholder[1]]) && !$optional) return null;
        }

        $uri = preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => (string) ($params[$m[1]] ?? ''), $route->uri());

        return '/' . trim(preg_replace('#/+#', '/', $uri), '/');
    }
}

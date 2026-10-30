<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use App\Models\User;
use App\Models\Worker;
use Database\Seeders\DemoAnalyticsSeeder;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * Opens every page as each role on the demo data and reads what it shows:
 *  - every form posts to a route that exists with that method, and POST forms carry the CSRF token;
 *  - every internal link opens (no 404 or 500), and no role is shown a link it may not open (403);
 *  - form fields have a label, buttons and links have a name, and an id is used once per page.
 * Every finding fails the test; the full report (and the pages each role could not open) is in storage/logs/ui-audit.txt.
 */
class PageLinksAndFormsTest extends TestCase
{
    use RefreshDatabase;

    // Same skip list as PageSmokeTest: files and exports are opened as links below, not crawled as pages
    private const SKIP = '#^(api/|_ignition|sanctum|up$|storage|webhook|auth/google|logout|mobile-scanner|docs/|.*(pdf|export|download|csv|print|receipt|signature-sheet|photo|vcard|send-whatsapp).*)#i';

    private array $problems = [];
    private array $notes = [];
    private array $notOpened = [];

    public function test_links_and_forms_work_for_every_role(): void
    {
        $this->seed();
        $this->seed(DemoAnalyticsSeeder::class);

        $worker = Worker::create(['name' => 'Audit Worker', 'phone' => '9000099922', 'daily_rate' => 750, 'is_active' => true]);
        $workerUser = User::factory()->create(['role' => 'worker', 'email' => 'worker_9000099922@cctv-crm.local']);
        $worker->update(['user_id' => $workerUser->id]);

        // The demo data has no project: add one with done, open and blocked tasks so the project pages are opened too
        $admin = User::where('email', 'test@example.com')->first();
        $technician = User::where('email', 'bob@example.com')->first();
        $project = Project::create([
            'project_code' => 'PRJ-AUDIT-1', 'title' => 'Audit CCTV Site', 'project_type' => 'hardware_cctv',
            'status' => 'in_progress', 'created_by' => $admin->id, 'lead_technician_id' => $technician->id,
        ]);
        ProjectTask::create(['project_id' => $project->id, 'phase_name' => 'Phase 1: Survey', 'title' => 'Site walk-through', 'status' => 'completed', 'completed_at' => now(), 'assigned_to' => $technician->id]);
        $open = ProjectTask::create(['project_id' => $project->id, 'phase_name' => 'Phase 2: Cabling', 'title' => 'Pull Cat6 cables', 'status' => 'pending', 'priority' => 'high', 'due_date' => now()->subDay(), 'assigned_to' => $technician->id]);
        ProjectTaskChecklist::create(['project_task_id' => $open->id, 'item_text' => 'Test each cable run', 'is_completed' => false]);
        ProjectTask::create(['project_id' => $project->id, 'phase_name' => 'Phase 2: Cabling', 'title' => 'Core drilling', 'status' => 'blocked', 'blocker_reason' => 'Waiting for building permission', 'blocked_at' => now()]);

        $users = [
            'admin' => User::where('email', 'test@example.com')->first(),
            'staff' => User::where('email', 'alex@example.com')->first(),
            'technician' => User::where('email', 'bob@example.com')->first(),
            'worker' => $workerUser,
            'customer' => User::factory()->create(['role' => 'customer']),
        ];

        foreach ($users as $role => $user) {
            $checkedLinkRoutes = [];
            foreach (Route::getRoutes() as $route) {
                $uri = $this->pageUri($route);
                if ($uri === null) continue;

                app('auth')->forgetGuards();
                $response = $this->actingAs($user)->get($uri);
                if ($response->getStatusCode() !== 200 || !str_contains((string) $response->headers->get('Content-Type'), 'html')) {
                    $this->notOpened[] = "{$role} {$uri}: " . $response->getStatusCode() . ($response->isRedirect() ? ' to ' . parse_url((string) $response->headers->get('Location'), PHP_URL_PATH) : '');
                    continue;
                }

                $this->inspect($role, $uri, $response->getContent(), $user, $checkedLinkRoutes);
            }
        }

        @file_put_contents(storage_path('logs/ui-audit.txt'), "PROBLEMS\n" . implode("\n", array_unique($this->problems)) . "\n\nNOTES\n" . implode("\n", array_unique($this->notes))
            . "\n\nNOT OPENED (redirects and refusals, for reading)\n" . implode("\n", $this->notOpened) . "\n");
        $this->assertSame([], array_values(array_unique($this->problems)), "Broken links or forms:\n" . implode("\n", array_unique($this->problems)));
        $this->assertSame([], array_values(array_unique($this->notes)), "Unlabelled fields, unnamed buttons or repeated ids:\n" . implode("\n", array_unique($this->notes)));
    }

    private function inspect(string $role, string $page, string $html, User $user, array &$checkedLinkRoutes): void
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();
        $xp = new DOMXPath($dom);

        // Forms: target route and method exist; POST forms carry the CSRF token
        foreach ($xp->query('//form') as $form) {
            /** @var DOMElement $form */
            if ($form->hasAttribute(':action') || $form->hasAttribute('x-bind:action')) continue;
            $action = $form->getAttribute('action');
            if (str_contains($action, '{{') || str_contains($action, '${') || str_starts_with($action, 'javascript')) continue;
            // An empty action that a script fills in when its modal opens (getElementById('x').action = ...)
            $formId = $form->getAttribute('id');
            if ($action === '' && $formId !== '' && str_contains($html, "getElementById('{$formId}')") && str_contains($html, '.action')) continue;
            $path = $this->internalPath($action === '' ? $page : $action);
            if ($path === null) continue;

            $method = strtoupper($form->getAttribute('method') ?: 'GET');
            if ($method === 'POST') {
                $override = $xp->query('.//input[@name="_method"]', $form)->item(0);
                if ($override instanceof DOMElement) $method = strtoupper($override->getAttribute('value'));
                if (!$xp->query('.//input[@name="_token"]', $form)->length) {
                    $this->problems[] = "{$role} {$page}: form to {$path} has no CSRF token (would fail with 419)";
                }
            }
            try {
                app('router')->getRoutes()->match(Request::create($path, $method));
            } catch (MethodNotAllowedHttpException) {
                $this->problems[] = "{$role} {$page}: form sends {$method} to {$path}, which does not accept {$method}";
            } catch (NotFoundHttpException) {
                $this->problems[] = "{$role} {$page}: form sends {$method} to {$path}, which does not exist";
            }
        }

        // Links: open one example per route per role
        foreach ($xp->query('//a[@href]') as $a) {
            /** @var DOMElement $a */
            // Links written inside script templates are filled in later
            if (str_contains($a->getAttribute('href'), '${')) continue;
            $path = $this->internalPath($a->getAttribute('href'));
            if ($path === null || $path === '/logout') continue;
            try {
                $target = app('router')->getRoutes()->match(Request::create($path, 'GET'));
            } catch (NotFoundHttpException) {
                $this->problems[] = "{$role} {$page}: link to {$path} goes nowhere (no such page)";
                continue;
            } catch (MethodNotAllowedHttpException) {
                $this->problems[] = "{$role} {$page}: link to {$path} needs a form (the route is not a GET)";
                continue;
            }
            $key = $target->uri();
            if (isset($checkedLinkRoutes[$key])) continue;
            $checkedLinkRoutes[$key] = true;

            app('auth')->forgetGuards();
            $label = mb_substr(trim(preg_replace('/\s+/', ' ', $a->textContent ?: $a->getAttribute('aria-label'))), 0, 40);
            $opened = $this->actingAs($user)->get($a->getAttribute('href'));
            $status = $opened->getStatusCode();
            $bouncedTo = $opened->isRedirect() ? (string) $opened->headers->get('Location') : '';
            if ($bouncedTo !== '' && (str_ends_with($bouncedTo, '/login') || ($bouncedTo === route('worker.portal') && $path !== '/worker/portal'))) {
                $this->problems[] = "{$role} {$page}: shows a link '{$label}' to {$path} that only sends this role back to " . parse_url($bouncedTo, PHP_URL_PATH);
            } elseif ($status === 404) {
                $this->problems[] = "{$role} {$page}: link to {$path} opens a 404";
            } elseif ($status >= 500) {
                $this->problems[] = "{$role} {$page}: link to {$path} opens a server error ({$status})";
            } elseif ($status === 403) {
                $this->problems[] = "{$role} {$page}: shows a link '{$label}' to {$path} that this role may not open (403)";
            }
        }

        // Naming: fields with a label, buttons and links with a name, ids used once
        foreach ($xp->query('//input[not(@type="hidden") and not(@type="submit") and not(@type="button") and not(@type="image") and not(@type="reset")] | //select | //textarea') as $field) {
            /** @var DOMElement $field */
            if ($field->getAttribute('aria-label') !== '' || $field->getAttribute('aria-labelledby') !== '' || $field->getAttribute('title') !== '') continue;
            if ($field->hasAttribute(':aria-label') || $field->hasAttribute('x-bind:aria-label')) continue;
            $id = $field->getAttribute('id');
            if ($id !== '' && $xp->query('//label[@for="' . $id . '"]')->length) continue;
            if ($xp->query('ancestor::label', $field)->length) continue;
            $this->notes[] = "{$page}: field " . ($field->getAttribute('name') ?: $field->nodeName . '#' . $id) . ' has no label  ' . $this->snippet($dom, $field);
        }
        foreach ($xp->query('//button | //a[@href]') as $el) {
            /** @var DOMElement $el */
            $text = trim(preg_replace('/\s+/', ' ', $el->textContent));
            if ($text !== '' || $el->getAttribute('aria-label') !== '' || $el->getAttribute('title') !== '' || $el->hasAttribute(':aria-label') || $el->hasAttribute('x-text')) continue;
            if ($xp->query('.//img[@alt!=""] | .//*[@x-text] | .//*[@x-html]', $el)->length) continue;
            $this->notes[] = "{$page}: " . $el->nodeName . ($el->getAttribute('href') ? ' to ' . $el->getAttribute('href') : '') . ' has no name (icon only, no aria-label)  ' . $this->snippet($dom, $el);
        }
        $ids = [];
        foreach ($xp->query('//*[@id]') as $el) {
            $ids[$el->getAttribute('id')] = ($ids[$el->getAttribute('id')] ?? 0) + 1;
        }
        foreach ($ids as $id => $count) {
            if ($count > 1) $this->notes[] = "{$page}: id \"{$id}\" is used {$count} times";
        }
    }

    /** The start of an element's markup, to find it in the views. */
    private function snippet(DOMDocument $dom, DOMElement $el): string
    {
        return mb_substr(preg_replace('/\s+/', ' ', $dom->saveHTML($el)), 0, 140);
    }

    /** The path of a same-site URL, or null for anchors, mail, phone, javascript and other sites. */
    private function internalPath(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || $url[0] === '#' || preg_match('#^(mailto:|tel:|javascript:|data:|sms:|whatsapp:)#i', $url)) return null;
        $parts = parse_url($url);
        if ($parts === false) return null;
        $host = $parts['host'] ?? null;
        if ($host !== null && !in_array($host, ['localhost', '127.0.0.1', parse_url(config('app.url'), PHP_URL_HOST)], true)) return null;
        $path = '/' . ltrim($parts['path'] ?? '/', '/');
        if (preg_match('#^/(build|storage|images|logos|favicon|logo)#', $path) || preg_match('/\.(png|jpe?g|svg|ico|css|js|webp|gif|woff2?)$/i', $path)) return null;

        return $path;
    }

    /** Same as PageSmokeTest::pageUri: a concrete URL for a GET page, using the first record of each bound model. */
    private function pageUri(RouteDefinition $route): ?string
    {
        if (!in_array('GET', $route->methods(), true) || preg_match(self::SKIP, $route->uri())) return null;

        $params = [];
        foreach ($route->signatureParameters(['subClass' => UrlRoutable::class]) as $parameter) {
            $class = $parameter->getType()?->getName();
            $model = $class ? $class::query()->first() : null;
            if (!$model) return null;
            $params[$parameter->getName()] = $model->getRouteKey();
        }
        preg_match_all('/\{(\w+)(\?)?\}/', $route->uri(), $placeholders, PREG_SET_ORDER);
        foreach ($placeholders as $placeholder) {
            if (!isset($params[$placeholder[1]]) && ($placeholder[2] ?? '') !== '?') return null;
        }
        $uri = preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => (string) ($params[$m[1]] ?? ''), $route->uri());

        return '/' . trim(preg_replace('#/+#', '/', $uri), '/');
    }
}

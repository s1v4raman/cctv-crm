<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Every page reads the same in light and dark mode: no white boxes or vanishing text in dark
 * mode, no washed-out colours in light mode. Colours that change with the theme come from the
 * tokens in resources/css/app.css (--link-color, --status-*-fg, --card-bg ...), not hex values.
 */
class ThemeConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_pages_follow_the_saved_theme(): void
    {
        $this->withUnencryptedCookie('crm_mode', 'night')->get('/forgot-password')
            ->assertOk()
            ->assertSee('class="h-full bg-white dark"', false)
            ->assertSee("localStorage.getItem('crm_mode')", false);

        $this->withUnencryptedCookie('crm_mode', 'day')->get('/reset-password/some-token?email=a@example.com')
            ->assertOk()
            ->assertDontSee('class="h-full bg-white dark"', false)
            ->assertSee("localStorage.getItem('crm_mode')", false);
    }

    public function test_worker_photo_box_has_dark_mode_colours(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('workers.create'))
            ->assertOk()
            ->assertSee('bg-slate-50 dark:bg-slate-800/40', false)
            ->assertSee('id="worker-photo-input" accept="image/*" class="cursor-pointer"', false)
            ->assertDontSee('file:bg-white', false);
    }

    public function test_views_take_status_and_accent_text_colours_from_theme_tokens(): void
    {
        $offenders = [];
        $files = (new Finder())->files()->in(resource_path('views'))->name('*.blade.php')
            ->notPath(['pdf', 'emails', 'docs'])->notName('*pdf*');

        foreach ($files as $file) {
            foreach (preg_split('/\R/', $file->getContents()) as $i => $line) {
                $hardCodedStatus = preg_match('/\$\w+StatusColor = \'#[0-9a-fA-F]{3,6}\'/', $line)
                    || preg_match('/class="status-text" style="color: #/', $line);
                // Accent as text: --link-color is the accent shade that reads on the current theme
                $rawAccentText = preg_match('/style="[^"]*(?<![-\w])color: ?var\(--crm-accent/', $line);

                if ($hardCodedStatus || $rawAccentText) {
                    $offenders[] = $file->getRelativePathname() . ':' . ($i + 1) . '  ' . trim($line);
                }
            }
        }

        $this->assertSame([], $offenders, "Use var(--status-success-fg) / var(--link-color) and friends:\n" . implode("\n", $offenders));
    }

    public function test_theme_guard_maps_light_only_classes_in_dark_mode(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        // Tokens exist for both themes
        $this->assertSame(2, substr_count($css, '--link-color: '), 'light and dark --link-color');
        $this->assertSame(2, substr_count($css, '--surface-subtle: '), 'light and dark --surface-subtle');

        // A light class without its dark: partner gets the dark colour
        $this->assertStringContainsString('html.dark :is(.bg-white, .bg-\[\#fff\], .bg-\[\#ffffff\]):not([class*="dark:bg-"])', $css);
        $this->assertStringContainsString('html.dark :is(.text-black, .text-gray-950, .text-gray-900', $css);
        $this->assertStringContainsString('input[type="file"]::file-selector-button', $css);
    }

    public function test_theme_script_gives_links_a_readable_shade_in_each_theme(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('--link-color: ${linkLight} !important;', false)
            ->assertSee('--link-color: ${linkDark} !important;', false)
            ->assertSee("window.addEventListener('beforeprint'", false);
    }
}

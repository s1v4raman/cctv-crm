<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Lead;
use App\Models\Project;
use App\Models\SiteSurvey;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * English / Tamil on the worker sign-in, worker portal and technician pages.
 */
class TamilLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function approvedWorker(string $name = 'Muthu Vel', string $phone = '9840123456'): Worker
    {
        return Worker::create([
            'name' => $name,
            'phone' => $phone,
            'daily_rate' => 750.00,
            'is_active' => true,
        ]);
    }

    public function test_guest_can_switch_the_worker_sign_in_page_to_tamil(): void
    {
        $this->from('/field/login')
            ->post(route('locale.update'), ['locale' => 'ta', 'portal' => 'worker'])
            ->assertRedirect('/field/login')
            ->assertCookie(SetLocale::COOKIE, 'ta')
            ->assertSessionHas('login_portal', 'worker');

        $this->withCookie(SetLocale::COOKIE, 'ta')
            ->get('/field/login')
            ->assertOk()
            ->assertSee('lang="ta"', false)
            ->assertSee('Noto+Sans+Tamil', false)
            ->assertSee('தினக்கூலி தொழிலாளர் உள்நுழைவு')
            ->assertSee('தொழிலாளர் முழுப் பெயர்')
            ->assertSee('மொபைல் எண்')
            ->assertSee('உள்நுழைந்து வருகையைத் திற')
            ->assertSee('தமிழ் விசைப்பலகை')
            ->assertSee("const speechLang = 'ta-IN'", false);
    }

    public function test_sign_in_page_stays_english_without_a_choice(): void
    {
        $this->get('/field/login')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Worker Full Name')
            ->assertSee('Sign In & Open Attendance')
            ->assertSee("const speechLang = 'en-IN'", false)
            ->assertDontSee('தொழிலாளர் முழுப் பெயர்');
    }

    public function test_unsupported_language_is_rejected(): void
    {
        $this->from('/login')
            ->post(route('locale.update'), ['locale' => 'fr'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('locale')
            ->assertCookieMissing(SetLocale::COOKIE);
    }

    public function test_worker_choice_is_saved_and_survives_signing_out_and_in(): void
    {
        $this->approvedWorker();

        $this->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840123456'])
            ->assertRedirect(route('worker.portal'));
        $user = User::where('email', 'worker_9840123456@cctv-crm.local')->firstOrFail();
        $this->assertNull($user->locale);

        $this->from(route('worker.portal'))
            ->post(route('locale.update'), ['locale' => 'ta'])
            ->assertRedirect(route('worker.portal'))
            ->assertCookie(SetLocale::COOKIE, 'ta');
        $this->assertSame('ta', $user->fresh()->locale);

        $this->get(route('worker.portal'))
            ->assertOk()
            ->assertSee('lang="ta"', false)
            ->assertSee('1. தினசரி வருகை (புகைப்படப் பதிவு)')
            ->assertSee('2. வேலைப் பதிவு (கேபிளிங், கேமராக்கள், BOM)')
            ->assertDontSee('Daily Attendance (Photo Punch)');

        $this->post('/logout');
        $this->assertGuest();

        // Signing in again (even from a browser without the cookie) brings Tamil back from the account
        $this->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840123456'])
            ->assertRedirect(route('worker.portal'))
            ->assertCookie(SetLocale::COOKIE, 'ta')
            ->assertSessionHas('status', 'வருக, Muthu Vel! தினக்கூலி தொழிலாளர் போர்டலில் உள்நுழைந்துள்ளீர்கள்.');

        $this->get(route('worker.portal'))
            ->assertOk()
            ->assertSee('1. தினசரி வருகை (புகைப்படப் பதிவு)');
    }

    public function test_choice_made_on_the_sign_in_page_is_saved_to_the_worker_account(): void
    {
        $worker = $this->approvedWorker();
        $user = User::factory()->create([
            'name' => $worker->name,
            'role' => 'worker',
            'email' => 'worker_9840123456@cctv-crm.local',
            'locale' => 'ta',
        ]);
        $worker->update(['user_id' => $user->id]);

        // The worker tapped English on the sign-in page of this phone
        $this->withCookie(SetLocale::COOKIE, 'en')
            ->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840123456'])
            ->assertRedirect(route('worker.portal'))
            ->assertCookie(SetLocale::COOKIE, 'en');

        $this->assertSame('en', $user->fresh()->locale);
        $this->get(route('worker.portal'))
            ->assertOk()
            ->assertSee('1. Daily Attendance (Photo Punch)');
    }

    public function test_tamil_name_typed_or_spoken_on_the_sign_in_page_is_stored_as_entered(): void
    {
        $tamilName = 'முருகன் செல்வம்';

        $this->withCookie(SetLocale::COOKIE, 'ta')
            ->post(route('login.worker'), ['name' => $tamilName, 'phone' => '98401 55555'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', "நன்றி, {$tamilName}. உங்கள் கோரிக்கை அலுவலகத்துக்கு அனுப்பப்பட்டது. நிர்வாகி ஒப்புதல் அளித்ததும் உள்நுழையலாம்.");

        $worker = Worker::where('phone', '9840155555')->firstOrFail();
        $this->assertSame($tamilName, $worker->name);
        $this->assertSame('pending', $worker->registration_status);

        // Once approved, the account and the portal carry the Tamil name unchanged
        $worker->update(['is_active' => true, 'registration_status' => 'approved']);

        $this->post(route('login.worker'), ['name' => $tamilName, 'phone' => '9840155555'])
            ->assertRedirect(route('worker.portal'));

        $this->assertDatabaseHas('users', ['name' => $tamilName, 'role' => 'worker']);
        $this->get(route('worker.portal'))
            ->assertOk()
            ->assertSee($tamilName);
    }

    public function test_sign_in_validation_errors_are_in_tamil(): void
    {
        $this->withCookie(SetLocale::COOKIE, 'ta')
            ->from('/field/login')
            ->post(route('login.worker'), ['name' => '', 'phone' => '123'])
            ->assertRedirect('/field/login')
            ->assertSessionHasErrors([
                'name' => 'பெயர் கட்டாயம் தேவை.',
                'phone' => 'மொபைல் எண் குறைந்தது 8 எழுத்துகள் இருக்க வேண்டும்.',
            ]);

        $this->withCookie(SetLocale::COOKIE, 'en')
            ->from('/field/login')
            ->post(route('login.worker'), ['name' => '', 'phone' => '9840123456'])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    public function test_worker_portal_messages_are_in_tamil(): void
    {
        $worker = Worker::create([
            'name' => 'Karthik Raja',
            'phone' => '9841122334',
            'daily_rate' => 750.00,
            'is_active' => true,
        ]);
        $user = User::factory()->create(['name' => $worker->name, 'role' => 'worker', 'locale' => 'ta']);
        $worker->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from(route('worker.portal'))
            ->post(route('worker.attendance.punch'), ['attendance_type' => 'night'])
            ->assertSessionHasErrors([
                'project_id' => 'திட்டம் / தளம் கட்டாயம் தேவை.',
                'attendance_type' => 'தேர்ந்தெடுத்த ஷிஃப்ட் வகை செல்லாது.',
            ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-TA-001',
            'title' => 'Tamil Test Site',
            'status' => 'in_progress',
            'created_by' => $admin->id,
            'per_metre_rate' => 8.00,
        ]);

        $this->actingAs($user)
            ->post(route('worker.attendance.punch'), ['project_id' => $project->id, 'attendance_type' => 'full_day'])
            ->assertRedirect(route('worker.portal'))
            ->assertSessionHas('status', '✓ Tamil Test Site — சரிபார்த்த புகைப்படத்துடன் வருகை வெற்றிகரமாகப் பதிவானது!');
    }

    public function test_technician_pages_switch_to_tamil(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        $this->actingAs($technician)
            ->from(route('technician.dashboard'))
            ->post(route('locale.update'), ['locale' => 'ta'])
            ->assertRedirect(route('technician.dashboard'));
        $this->assertSame('ta', $technician->fresh()->locale);

        $this->actingAs($technician->fresh())
            ->get(route('technician.dashboard'))
            ->assertOk()
            ->assertSee('lang="ta"', false)
            ->assertSee('Noto+Sans+Tamil', false)
            ->assertSee('டெக்னீஷியன் போர்டல் இயக்கத்தில்')
            ->assertSee('களப்பணி ஆணைகள்')
            ->assertSee('கள பொறியாளர்')
            ->assertSee('வெளியேறு')
            ->assertDontSee('Active Technician Portal');

        $lead = Lead::create(['customer_name' => 'Sailesh', 'phone' => '9876543210', 'status' => 'won']);
        $survey = SiteSurvey::create([
            'lead_id' => $lead->id,
            'surveyed_by' => $technician->id,
            'survey_date' => '2026-11-04',
            'site_address' => 'Anna Nagar, Chennai',
            'status' => 'pending',
        ]);

        $this->actingAs($technician->fresh())
            ->get(route('technician.surveys.show', $survey))
            ->assertOk()
            ->assertSee('தள ஆய்வு: Sailesh')
            ->assertSee('தள ஆய்வை முடித்து புகைப்படங்களைப் பதிவேற்று');

        $this->actingAs($technician->fresh())
            ->from(route('technician.surveys.show', $survey))
            ->post(route('technician.surveys.complete', $survey), ['camera_count_recommended' => 0])
            ->assertSessionHasErrors([
                'camera_count_recommended' => 'பரிந்துரைத்த கேமராக்கள் குறைந்தது 1 ஆக இருக்க வேண்டும்.',
                'dvr_location' => 'DVR / NVR இடம் கட்டாயம் தேவை.',
                'visit_notes' => 'வருகைக் குறிப்புகள் கட்டாயம் தேவை.',
            ]);
    }

    public function test_technician_sign_in_switches_to_tamil_and_stays_on_the_technician_tab(): void
    {
        $this->get('/field/login')
            ->assertOk()
            ->assertSee('name="portal" value="technician"', false);

        $this->from('/field/login')
            ->post(route('locale.update'), ['locale' => 'ta', 'portal' => 'technician'])
            ->assertRedirect('/field/login')
            ->assertCookie(SetLocale::COOKIE, 'ta')
            ->assertSessionHas('login_portal', 'technician');

        config(['app.login_portal' => 'technician']);

        $this->withCookie(SetLocale::COOKIE, 'ta')
            ->withSession(['login_portal' => 'technician'])
            ->get('/login')
            ->assertOk()
            ->assertSee('lang="ta"', false)
            ->assertSee("portal: 'technician'", false)
            ->assertSee('டெக்னீஷியன் உள்நுழைவு')
            ->assertSee('மின்னஞ்சல் முகவரி')
            ->assertSee('கடவுச்சொல்')
            ->assertSee('ஆபரேஷன்ஸ் ERP-இல் உள்நுழை')
            ->assertDontSee('Email Address');
    }

    public function test_choice_made_on_the_sign_in_page_is_saved_to_the_technician_account(): void
    {
        $technician = User::factory()->create(['role' => 'technician', 'locale' => 'en']);

        $this->withCookie(SetLocale::COOKIE, 'ta')
            ->post('/field/login', ['email' => $technician->email, 'password' => 'password'])
            ->assertRedirect(route('technician.dashboard'))
            ->assertCookie(SetLocale::COOKIE, 'ta');
        $this->assertSame('ta', $technician->fresh()->locale);

        $this->get(route('technician.dashboard'))
            ->assertOk()
            ->assertSee('lang="ta"', false)
            ->assertSee('களப்பணி ஆணைகள்');
    }

    public function test_office_accounts_stay_in_english_whatever_the_cookie_says(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'ta']);
        $this->assertNull($admin->fresh()->locale);

        $this->actingAs($admin)
            ->withCookie(SetLocale::COOKIE, 'ta')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertDontSee('Noto+Sans+Tamil', false);
        $this->assertSame('en', app()->getLocale());
    }

    public function test_microphone_is_allowed_only_where_there_is_voice_input(): void
    {
        $this->get('/field/login')->assertHeader('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=()');
        $this->get('/login')->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        $technician = User::factory()->create(['role' => 'technician']);
        $this->actingAs($technician)
            ->get(route('technician.dashboard'))
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=()');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }

    public function test_worker_and_technician_text_boxes_have_the_mic_and_tamil_keyboard(): void
    {
        $worker = $this->approvedWorker();
        $workerUser = User::factory()->create(['name' => $worker->name, 'role' => 'worker', 'locale' => 'ta']);
        $worker->update(['user_id' => $workerUser->id]);

        $this->actingAs($workerUser)
            ->get(route('worker.portal'))
            ->assertOk()
            ->assertSee("const speechLang = 'ta-IN'", false)
            ->assertSee('x-data="tamilInput()"', false)
            ->assertSee('id="work_notes"', false)
            ->assertSee('தமிழ் விசைப்பலகை')
            ->assertSee('பேசுங்கள்');

        $technician = User::factory()->create(['role' => 'technician', 'locale' => 'en']);
        $lead = Lead::create(['customer_name' => 'Sailesh', 'phone' => '9876543210', 'status' => 'won']);
        $survey = SiteSurvey::create([
            'lead_id' => $lead->id,
            'surveyed_by' => $technician->id,
            'survey_date' => '2026-11-04',
            'site_address' => 'Anna Nagar, Chennai',
            'status' => 'pending',
        ]);

        // In English the mic writes English, and the Tamil keyboard is still there
        $this->actingAs($technician)
            ->get(route('technician.surveys.show', $survey))
            ->assertOk()
            ->assertSee("const speechLang = 'en-IN'", false)
            ->assertSee('x-data="tamilInput()"', false)
            ->assertSee('name="visit_notes"', false)
            ->assertSee('Tamil keyboard');
    }

    public function test_office_pages_have_no_mic_or_tamil_keyboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('workers.attendance.index'))
            ->assertOk()
            ->assertSee('id="att-notes"', false)
            ->assertDontSee('tamilInput(', false)
            ->assertDontSee('Tamil keyboard');
    }

    public function test_every_worker_and_technician_string_has_a_tamil_translation(): void
    {
        $tamil = json_decode(file_get_contents(lang_path('ta.json')), true, flags: JSON_THROW_ON_ERROR);

        $files = [
            resource_path('views/auth/login.blade.php'),
            resource_path('views/worker/portal.blade.php'),
            resource_path('views/technician/dashboard.blade.php'),
            resource_path('views/technician/surveys/show.blade.php'),
            resource_path('views/components/mark-attendance-modal.blade.php'),
            resource_path('views/components/barcode-scanner.blade.php'),
            resource_path('views/components/language-switch.blade.php'),
            resource_path('views/components/close-button.blade.php'),
            resource_path('views/components/tamil-input.blade.php'),
            resource_path('views/components/tamil-input-script.blade.php'),
            resource_path('views/components/tamil-keyboard.blade.php'),
            app_path('Http/Controllers/WorkerPortalController.php'),
            app_path('Http/Controllers/Technician/TechnicianPortalController.php'),
        ];

        $keys = [];
        foreach ($files as $file) {
            preg_match_all('/__\(\s*(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")/', file_get_contents($file), $matches);
            foreach ($matches[1] as $literal) {
                $body = substr($literal, 1, -1);
                $keys[] = $literal[0] === "'"
                    ? str_replace(["\\'", '\\\\'], ["'", '\\'], $body)
                    : stripcslashes($body);
            }
        }

        // Labels that reach __() through a variable on the technician pages
        $keys = array_merge($keys, array_values(Project::statusOptions()), array_values(Project::projectTypeOptions()), ['Field Engineer', 'Field Worker']);

        $missing = array_values(array_diff(array_unique($keys), array_keys($tamil)));
        $this->assertSame([], $missing, 'Strings without a Tamil translation in lang/ta.json');
        $this->assertGreaterThan(400, count($tamil));
    }
}

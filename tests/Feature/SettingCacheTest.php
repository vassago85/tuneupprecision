<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_reads_come_from_one_query_per_request(): void
    {
        Setting::put('eft.bank_name', 'FNB');
        Setting::put('eft.branch_code', '250655');
        Setting::forgetLoaded();

        DB::enableQueryLog();
        Setting::get('eft.bank_name');
        Setting::get('eft.branch_code');
        Setting::get('missing', 'fallback');

        $this->assertLessThanOrEqual(1, count(DB::getQueryLog()));
    }

    public function test_every_kind_of_write_is_seen_straight_away(): void
    {
        Setting::put('eft.bank_name', 'FNB');
        $this->assertSame('FNB', Setting::get('eft.bank_name'));

        Setting::put('eft.bank_name', 'Capitec');
        $this->assertSame('Capitec', Setting::get('eft.bank_name'));

        Setting::query()->where('key', 'eft.bank_name')->first()->update(['value' => 'Nedbank']);
        $this->assertSame('Nedbank', Setting::get('eft.bank_name'));

        Setting::query()->where('key', 'eft.bank_name')->first()->delete();
        $this->assertSame('default', Setting::get('eft.bank_name', 'default'));
    }

    public function test_a_null_value_falls_back_to_the_default(): void
    {
        Setting::put('mail.notify_email', null);

        $this->assertSame('info@example.com', Setting::get('mail.notify_email', 'info@example.com'));
    }

    public function test_a_long_running_worker_sees_changes_made_elsewhere(): void
    {
        Setting::put('mail.mailer', 'log');
        $this->assertSame('log', Setting::get('mail.mailer'));

        // Another process (the web app) saved a change and cleared the shared
        // cache; this process still holds its own copy until it forgets it.
        DB::table('settings')->where('key', 'mail.mailer')->update(['value' => 'mailgun']);
        cache()->forget('settings.all');
        $this->assertSame('log', Setting::get('mail.mailer'));

        Setting::forgetLoaded();
        $this->assertSame('mailgun', Setting::get('mail.mailer'));
    }
}

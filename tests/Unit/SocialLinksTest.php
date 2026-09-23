<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\SocialLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_settings_are_null_and_hidden(): void
    {
        $this->assertSame([
            'instagram' => null,
            'facebook' => null,
            'youtube' => null,
            'whatsapp' => null,
        ], SocialLinks::details());

        $this->assertSame([], SocialLinks::visible());
    }

    public function test_whitespace_is_treated_as_blank(): void
    {
        Setting::put('social.instagram', '   ');

        $this->assertNull(SocialLinks::details()['instagram']);
        $this->assertSame([], SocialLinks::visible());
    }

    public function test_saved_urls_are_trimmed_and_shown_in_footer_order(): void
    {
        Setting::put('social.youtube', '  https://youtube.com/@tuneupprecision  ');
        Setting::put('social.instagram', 'https://instagram.com/tuneupprecision');

        $this->assertSame(
            'https://youtube.com/@tuneupprecision',
            SocialLinks::details()['youtube'],
        );

        $this->assertSame([
            [
                'key' => 'instagram',
                'label' => 'Instagram',
                'url' => 'https://instagram.com/tuneupprecision',
            ],
            [
                'key' => 'youtube',
                'label' => 'YouTube',
                'url' => 'https://youtube.com/@tuneupprecision',
            ],
        ], SocialLinks::visible());
    }

    public function test_non_http_urls_stay_in_the_form_but_are_not_shown(): void
    {
        Setting::put('social.facebook', 'ftp://example.com/tuneup');

        $this->assertSame('ftp://example.com/tuneup', SocialLinks::details()['facebook']);
        $this->assertSame([], SocialLinks::visible());
    }
}

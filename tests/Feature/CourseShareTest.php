<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CourseTemplate;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_page_uses_its_own_tile_as_the_share_image(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');

        $course = CourseTemplate::query()->where('slug', 'precision-reloading')->firstOrFail();
        $course->addMedia(public_path('images/logo.png'))
            ->preservingOriginal()
            ->toMediaCollection('thumbnail');

        $image = $course->fresh()->shareImageUrl();
        $this->assertNotNull($image);
        $this->assertStringNotContainsString('hero-loop-poster', $image);
        $this->assertStringNotContainsString('og-share.jpg', $image);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Precision Reloading')
            ->assertSee('property="og:image" content="'.$image.'"', false)
            ->assertSee('data-share="'.route('courses.show', $course).'"', false)
            ->assertDontSee('hero-loop-poster.webp', false);
    }

    public function test_course_without_a_tile_falls_back_to_the_brand_card(): void
    {
        $this->seed(DatabaseSeeder::class);

        $course = CourseTemplate::query()->where('slug', 'handgun-fundamentals')->firstOrFail();

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('images/og-share.jpg', false)
            ->assertDontSee('hero-loop-poster.webp', false);
    }

    public function test_inactive_course_is_not_shareable(): void
    {
        $this->seed(DatabaseSeeder::class);

        $course = CourseTemplate::query()->where('slug', 'prs-match-skills')->firstOrFail();
        $course->update(['is_active' => false]);

        $this->get('/courses/prs-match-skills')->assertNotFound();
    }

    public function test_sitemap_lists_each_active_course(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/courses/precision-reloading', false)
            ->assertSee('/courses/applied-long-range', false)
            ->assertSee('/courses/zero-to-first-steel', false);
    }
}

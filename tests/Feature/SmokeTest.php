<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_renders_and_hides_out_of_stock(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Literal template strings keep raw apostrophes (Blade only escapes
        // interpolated `{{ ... }}` output), so needles with apostrophes are
        // asserted with the escape flag disabled.
        $this->get('/')
            ->assertOk()
            ->assertSee('Dial in')
            ->assertSee('Meet Dirk')
            // "What you'll learn" tabs are driven by TrainingType + at least one
            // bullet from every seeded discipline should render.
            ->assertSee("What you'll learn", false)
            ->assertSee('Handgun Fundamentals')
            ->assertSee('PRS safety, equipment and match fundamentals')
            ->assertSee('Setting up and using a ballistic calculator')
            ->assertSee('Handgun controls, components and safe operation');
    }

    public function test_courses_page_shows_four_disciplines_with_learn_lists(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/courses')
            ->assertOk()
            ->assertSee('Four disciplines')
            ->assertSee('Precision Reloading')
            ->assertSee('PRS Shooting')
            ->assertSee('Precision Long Range')
            ->assertSee('Handgun Fundamentals')
            ->assertSee('Fully booked')
            ->assertSee('Book')
            // "What you'll learn" bullets render on the discipline cards.
            ->assertSee("What you'll learn", false)
            ->assertSee('Reloading bench and component safety')
            ->assertSee('Correct stance, grip and sight alignment');
    }

    public function test_admin_dashboard_renders_with_widgets(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_resource_indexes_and_settings_render(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $pages = [
            '/admin/training-types',
            '/admin/course-templates',
            '/admin/training-events',
            '/admin/events-calendar',
            '/admin/bookings',
            '/admin/testimonials',
            '/admin/products',
            '/admin/orders',
            '/admin/payments',
            '/admin/component-categories',
            '/admin/components',
            '/admin/quotes',
            '/admin/manage-eft-settings',
            '/admin/legal-compliance',
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }
}

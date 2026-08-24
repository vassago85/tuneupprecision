<?php

declare(strict_types=1);

namespace Tests\Feature;

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

        $this->get('/')
            ->assertOk()
            ->assertSee('Tune Up Trucker Cap')
            ->assertDontSee('Mini IPSC Gong'); // out of stock => hidden by available()
    }

    public function test_courses_page_shows_three_disciplines_with_dates(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/courses')
            ->assertOk()
            ->assertSee('Precision Reloading')
            ->assertSee('PRS Shooting')
            ->assertSee('Precision Long Range')
            ->assertSee('Fully booked')
            ->assertSee('Book');
    }

    public function test_admin_dashboard_renders_with_widgets(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_resource_indexes_and_settings_render(): void
    {
        $admin = User::factory()->create();

        $pages = [
            '/admin/training-types',
            '/admin/course-templates',
            '/admin/training-events',
            '/admin/events-calendar',
            '/admin/bookings',
            '/admin/products',
            '/admin/orders',
            '/admin/payments',
            '/admin/component-categories',
            '/admin/components',
            '/admin/quotes',
            '/admin/manage-eft-settings',
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }
}

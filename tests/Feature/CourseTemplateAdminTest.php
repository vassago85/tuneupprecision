<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\CourseTemplates\Pages\EditCourseTemplate;
use App\Models\CourseTemplate;
use App\Models\TrainingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseTemplateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_sentence_typed_into_the_slug_is_saved_as_a_clean_url(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));

        $template = CourseTemplate::create([
            'training_type_id' => TrainingType::factory()->create()->id,
            'title' => 'One on one reloading',
            'slug' => 'one-on-one-reloading',
            'base_price_cents' => 0,
            'default_capacity' => 1,
            'is_active' => true,
        ]);

        Livewire::test(EditCourseTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm(['slug' => 'Setup at your home, full one on one reloading course '])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('setup-at-your-home-full-one-on-one-reloading-course', $template->refresh()->slug);
    }
}

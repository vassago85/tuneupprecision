<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CourseTemplate;
use App\Models\TrainingEvent;
use Illuminate\Contracts\View\View;

class CourseController extends Controller
{
    public function show(CourseTemplate $course): View
    {
        $course->loadMissing('trainingType');

        abort_unless($course->is_active && $course->trainingType?->is_active, 404);

        $events = $course->trainingEvents()
            ->with('courseTemplate')
            ->onCoursesPage()
            ->get();

        $prices = $events->map(fn (TrainingEvent $e) => $e->effectivePriceCents())->filter()->unique();

        $templates = CourseTemplate::query()
            ->where('training_type_id', $course->training_type_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('courses.show', [
            'course' => $course,
            'type' => $course->trainingType,
            'events' => $events,
            'templates' => $templates,
            'fromPriceCents' => $prices->min() ? (int) $prices->min() : (int) $course->base_price_cents,
            'priceIsFrom' => $prices->count() > 1,
        ]);
    }
}

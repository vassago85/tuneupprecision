<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CourseTemplate;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use Illuminate\Contracts\View\View;

class CourseController extends Controller
{
    /**
     * Public agenda: one card per active training discipline, each with the
     * "What you'll learn" list and a compact list of upcoming dates.
     * Fully-booked dates DO show (as "Fully booked" on the row).
     */
    public function index(): View
    {
        $trainingTypes = TrainingType::query()
            ->activeOrdered()
            ->with(['courseTemplates' => fn ($q) => $q->where('is_active', true)->orderBy('id')->with('media')])
            ->get();

        $upcomingEvents = TrainingEvent::query()
            ->with('courseTemplate.trainingType')
            ->onCoursesPage()
            ->get()
            ->groupBy(fn (TrainingEvent $event): ?int => $event->courseTemplate?->training_type_id);

        // One payload per active training type — its representative template
        // (for the on-card blurb/specs) plus every upcoming date on it.
        $disciplines = $trainingTypes->map(function (TrainingType $type) use ($upcomingEvents): array {
            $events = ($upcomingEvents->get($type->id) ?? collect())
                ->sortBy(fn (TrainingEvent $e) => $e->starts_on->timestamp)
                ->values();

            $representative = $type->courseTemplates->first();

            $prices = $events->map(fn (TrainingEvent $e) => $e->effectivePriceCents())->filter()->unique();
            $fromPriceCents = $prices->min();

            return [
                'type' => $type,
                'representative' => $representative,
                'templates' => $type->courseTemplates,
                'events' => $events,
                'from_price_cents' => $fromPriceCents ? (int) $fromPriceCents : ($representative?->base_price_cents ?? 0),
                'price_is_from' => $prices->count() > 1,
            ];
        })->values();

        return view('courses', ['disciplines' => $disciplines]);
    }

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

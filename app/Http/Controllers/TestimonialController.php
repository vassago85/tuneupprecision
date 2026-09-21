<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TestimonialSource;
use App\Models\Testimonial;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;

class TestimonialController extends Controller
{
    /**
     * Public submit form. Reached via a signed URL emailed to attendees after
     * training. The `signed` middleware (on the route) enforces the signature;
     * we only decorate the pre-filled context here.
     */
    public function create(Request $request): ViewContract
    {
        $trainingType = null;
        if ($typeId = $request->integer('training_type_id')) {
            $trainingType = TrainingType::find($typeId);
        }

        $trainingEvent = null;
        if ($eventId = $request->integer('training_event_id')) {
            $trainingEvent = TrainingEvent::with('courseTemplate.trainingType', 'trainingType')->find($eventId);
            // If the URL only pinned an event, fall back to its discipline.
            if ($trainingType === null && $trainingEvent !== null) {
                $trainingType = $trainingEvent->courseTemplate?->trainingType
                    ?? $trainingEvent->trainingType;
            }
        }

        return View::make('testimonials.submit', [
            'authorName' => trim((string) $request->query('name', '')),
            'trainingType' => $trainingType,
            'trainingEvent' => $trainingEvent,
            // Rendered only when the signed link did not pin a discipline —
            // gives the shooter a manual fallback. Passed from the controller
            // to keep Blade free of inline imports.
            'trainingTypeOptions' => $trainingType
                ? collect()
                : TrainingType::query()->activeOrdered()->get(),
        ]);
    }

    /**
     * Persist a submission from the public form. Every submission lands as
     * pending; Dirk approves in Filament before it appears on the site.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:120'],
            'training_type_id' => ['required', 'integer', 'exists:training_types,id'],
            'training_event_id' => ['nullable', 'integer', 'exists:training_events,id'],
            'body' => ['required', 'string', 'min:10', 'max:800'],
        ]);

        Testimonial::create([
            'training_type_id' => (int) $validated['training_type_id'],
            'training_event_id' => $validated['training_event_id'] ?? null,
            'author_name' => trim($validated['author_name']),
            'body' => trim($validated['body']),
            'is_approved' => false,
            'source' => TestimonialSource::Shooter,
            'submitted_at' => now(),
        ]);

        return redirect()->route('testimonials.thanks');
    }

    /**
     * Thank-you page with a WhatsApp share button.
     */
    public function thanks(): ViewContract
    {
        return View::make('testimonials.thanks');
    }
}

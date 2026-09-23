<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TestimonialSource;
use App\Mail\TestimonialCopy;
use App\Models\Testimonial;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use App\Support\BusinessDetails;
use App\Support\LegalIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;

class TestimonialController extends Controller
{
    /**
     * Public submit form. The stable URL is copied from the admin dashboard
     * and sent to shooters. Invite emails still pass name, discipline, and
     * event as query params so those links open pre-filled.
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
            'authorEmail' => trim((string) $request->query('email', '')),
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
            'author_email' => ['required', 'email:rfc', 'max:255'],
            'training_type_id' => ['required', 'integer', 'exists:training_types,id'],
            'training_event_id' => ['nullable', 'integer', 'exists:training_events,id'],
            'body' => ['required', 'string', 'min:10', 'max:800'],
        ]);

        $testimonial = Testimonial::create([
            'training_type_id' => (int) $validated['training_type_id'],
            'training_event_id' => $validated['training_event_id'] ?? null,
            'author_name' => trim($validated['author_name']),
            'author_email' => mb_strtolower(trim($validated['author_email'])),
            'body' => trim($validated['body']),
            'is_approved' => false,
            'source' => TestimonialSource::Shooter,
            'submitted_at' => now(),
        ]);

        $testimonial->load('trainingType');

        $dirk = BusinessDetails::details()['email'] ?? LegalIdentity::email();

        Mail::to($dirk)->queue(new TestimonialCopy($testimonial, false));
        Mail::to($testimonial->author_email)->queue(new TestimonialCopy($testimonial, true));

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

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PlaceBooking;
use App\Mail\BookingPlaced;
use App\Models\Booking;
use App\Models\TrainingEvent;
use App\Support\Eft;
use App\Support\OwnerInbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const int MIN_SUBMIT_SECONDS = 3;

    public function create(TrainingEvent $event, PlaceBooking $place): View
    {
        abort_unless($place->isBookable($event), 404);

        $event->loadMissing('courseTemplate.trainingType');

        return view('bookings.create', [
            'event' => $event,
            'course' => $event->courseTemplate,
            'holdHours' => $place->holdHours(),
        ]);
    }

    public function store(Request $request, TrainingEvent $event, PlaceBooking $place): RedirectResponse
    {
        abort_unless($place->isBookable($event), 404);

        if (filled($request->input('company'))) {
            return redirect()->route('courses');
        }

        $renderedAt = (int) $request->input('ts', 0);
        if ($renderedAt > 0 && (now()->timestamp - $renderedAt) < self::MIN_SUBMIT_SECONDS) {
            return redirect()->route('courses');
        }

        $event->loadMissing('courseTemplate');
        $seatsLeft = $event->seatsLeft();

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'rifle' => ['nullable', 'string', 'max:160'],
            'seats' => ['required', 'integer', 'min:1', 'max:'.$seatsLeft],
            'terms' => ['accepted'],
        ]);

        try {
            $booking = $place->place($event, [
                'customer_name' => trim($data['customer_name']),
                'email' => mb_strtolower(trim($data['email'])),
                'phone' => trim($data['phone']),
                'rifle' => filled($data['rifle'] ?? null) ? trim((string) $data['rifle']) : null,
                'seats' => (int) $data['seats'],
            ]);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('bookings.create', $event)
                ->withErrors($exception->errors())
                ->withInput();
        }

        $booking->load('trainingEvent.courseTemplate');

        Mail::to($booking->email)->queue(new BookingPlaced($booking, true));
        Mail::to(OwnerInbox::email())->queue(new BookingPlaced($booking, false));

        return redirect()
            ->route('bookings.confirmed')
            ->with('booking.confirmation', $booking->id);
    }

    public function confirmed(): View|RedirectResponse
    {
        $bookingId = session('booking.confirmation');
        $booking = is_numeric($bookingId)
            ? Booking::query()->with('trainingEvent.courseTemplate')->find((int) $bookingId)
            : null;

        if ($booking === null) {
            return redirect()->route('courses');
        }

        return view('bookings.confirmed', [
            'booking' => $booking,
            'event' => $booking->trainingEvent,
            'eft' => Eft::details(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PlaceBooking;
use App\Models\TrainingEvent;
use App\Support\ContactLink;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Visual month grid. `?month=YYYY-MM` picks the month; default is the current
 * month. The grid always spans full weeks (Mon–Sun) so partial weeks at
 * either end are filled with adjacent-month days (dimmed).
 */
class CalendarController extends Controller
{
    public function __invoke(Request $request, PlaceBooking $booking): View
    {
        $monthParam = (string) $request->query('month', '');
        try {
            $month = $monthParam !== ''
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (Throwable) {
            $month = Carbon::now()->startOfMonth();
        }

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // The calendar shows both training dates and competitions Dirk is
        // attending — /courses stays training-only (that's where you book a seat).
        $events = TrainingEvent::query()
            ->with('courseTemplate.trainingType', 'trainingType')
            ->publiclyVisible()
            ->where(function ($q) use ($gridStart, $gridEnd) {
                $q->whereBetween('starts_on', [$gridStart->toDateString(), $gridEnd->toDateString()])
                    ->orWhereBetween('ends_on', [$gridStart->toDateString(), $gridEnd->toDateString()])
                    ->orWhere(function ($qq) use ($gridStart, $gridEnd) {
                        $qq->where('starts_on', '<=', $gridStart->toDateString())
                            ->where('ends_on', '>=', $gridEnd->toDateString());
                    });
            })
            ->orderBy('starts_on')
            ->get();

        return view('calendar', [
            'month' => $month,
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'days' => $this->days($month, $gridStart, $gridEnd, $events),
            'eventsPayload' => $events->mapWithKeys(fn (TrainingEvent $event): array => [
                $event->id => $this->payload($event, $booking),
            ])->all(),
        ]);
    }

    /**
     * @param  Collection<int, TrainingEvent>  $events
     * @return list<array{date: Carbon, inMonth: bool, isToday: bool, events: list<TrainingEvent>}>
     */
    private function days(Carbon $month, Carbon $gridStart, Carbon $gridEnd, Collection $events): array
    {
        // Index events onto every day they cover within the visible grid.
        $eventsByDay = [];
        foreach ($events as $event) {
            $end = ($event->ends_on ?? $event->starts_on)->copy();
            for ($d = $event->starts_on->copy(); $d->lte($end); $d->addDay()) {
                $eventsByDay[$d->toDateString()][] = $event;
            }
        }

        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $days[] = [
                'date' => $d->copy(),
                'inMonth' => $d->month === $month->month,
                'isToday' => $d->isToday(),
                'events' => $eventsByDay[$d->toDateString()] ?? [],
            ];
        }

        return $days;
    }

    /**
     * Compact payload for the click-to-open modal.
     *
     * @return array<string, mixed>
     */
    private function payload(TrainingEvent $event, PlaceBooking $booking): array
    {
        $isComp = $event->isCompetition();
        $start = $event->starts_on;
        $end = $event->ends_on;
        $dateLabel = ($end && $end->ne($start))
            ? $start->format('D d M').' – '.$end->format('D d M Y')
            : $start->format('D d M Y');

        $priceCents = $isComp
            ? (int) ($event->entry_fee_cents ?? 0)
            : $event->effectivePriceCents();

        $blurb = $isComp
            ? ($event->dirk_role
                ? "Dirk is at this match — {$event->dirk_role}."
                : 'Dirk is attending this match — join him on the line.')
            : $event->courseTemplate?->blurb;

        if ($isComp) {
            $actionLabel = $event->external_url ? 'Match info' : 'Contact Dirk';
            $actionHref = $event->external_url ?? ContactLink::url($event->displayTitle());
            $actionExternal = (bool) $event->external_url;
        } else {
            $bookable = $booking->isBookable($event);
            $actionLabel = $bookable ? 'Book this date' : 'Enquire about this date';
            $actionHref = $bookable
                ? route('bookings.create', $event)
                : ContactLink::url(($event->isFull() ? 'Fully booked: ' : 'About: ').($event->courseTemplate?->title ?? 'Training').' · '.$dateLabel);
            $actionExternal = false;
        }

        return [
            'kind' => $isComp ? 'competition' : 'training',
            'title' => $isComp ? $event->displayTitle() : ($event->courseTemplate?->title ?? 'Training'),
            'discipline' => $event->disciplineName(),
            'level' => $event->courseTemplate?->level,
            'date_label' => $dateLabel,
            'venue' => $event->venue,
            'price' => $priceCents > 0 ? Money::format($priceCents, false) : null,
            'price_note' => $isComp ? 'Entry fee' : 'Per shooter',
            'seats_note' => $isComp
                ? null
                : ($event->isFull() ? 'Fully booked' : $event->seatsLeft().' of '.$event->capacity.' seats left'),
            'dirk_role' => $event->dirk_role,
            'blurb' => $blurb,
            'action_label' => $actionLabel,
            'action_href' => $actionHref,
            'action_external' => $actionExternal,
        ];
    }
}

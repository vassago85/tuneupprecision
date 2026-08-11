<?php

declare(strict_types=1);

use App\Enums\EventKind;
use App\Http\Controllers\NewsletterController;
use App\Models\Product;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Landing page shows a single "next event" card — the soonest publicly
    // visible training date (never a competition/guest event).
    $nextEvent = TrainingEvent::query()
        ->with('courseTemplate.trainingType')
        ->where('kind', EventKind::Training->value)
        ->publiclyVisible()
        ->upcoming()
        ->first();

    return view('home', [
        'nextEvent' => $nextEvent,
    ]);
})->name('home');

Route::get('/courses', function (Request $request) {
    // Public agenda: every upcoming training date, month-grouped, with a
    // discipline filter (Reloading, PRS, ELR, ...). Fully-booked dates DO show
    // (as "Fully booked" on the card).
    $trainingTypes = TrainingType::query()->activeOrdered()->get();
    $selectedType = $request->query('type');

    $eventsByMonth = TrainingEvent::query()
        ->with('courseTemplate.trainingType')
        ->where('kind', EventKind::Training->value)
        ->publiclyVisible()
        ->upcoming()
        ->when($selectedType, fn ($query, $slug) => $query->whereHas(
            'courseTemplate.trainingType',
            fn ($q) => $q->where('slug', $slug)
        ))
        ->get()
        ->groupBy(fn (TrainingEvent $event): string => $event->starts_on->format('F Y'));

    return view('courses', [
        'trainingTypes' => $trainingTypes,
        'selectedType' => $selectedType,
        'eventsByMonth' => $eventsByMonth,
    ]);
})->name('courses');

Route::get('/calendar', function (Request $request) {
    // Visual month grid. `?month=YYYY-MM` picks the month; default is the
    // current month. The grid always spans full weeks (Mon–Sun) so partial
    // weeks at either end are filled with adjacent-month days (dimmed).
    $monthParam = (string) $request->query('month', '');
    try {
        $month = $monthParam !== ''
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : Carbon::now()->startOfMonth();
    } catch (\Throwable) {
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

    // Index events onto every day they cover within the visible grid.
    $eventsByDay = [];
    foreach ($events as $event) {
        $start = $event->starts_on->copy();
        $end = ($event->ends_on ?? $event->starts_on)->copy();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $eventsByDay[$key] ??= [];
            $eventsByDay[$key][] = $event;
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

    return view('calendar', [
        'month' => $month,
        'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
        'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
        'days' => $days,
    ]);
})->name('calendar');

Route::get('/shop', function () {
    // Full product listing (out-of-stock / inactive items simply don't show).
    $products = Product::query()->available()->latest()->get();

    return view('shop', [
        'products' => $products,
    ]);
})->name('shop');

// Newsletter subscribe (public form) + one-click unsubscribe.
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

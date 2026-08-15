<?php

declare(strict_types=1);

use App\Enums\EventKind;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NewsletterController;
use App\Livewire\RifleBuilder;
use App\Models\Product;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use App\Models\Video;
use App\Support\Money;
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
    // discipline filter (Reloading, PRS, Precision Long Range, ...). Fully-booked dates DO show
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

    // Compact payload for the click-to-open modal — one entry per event, keyed
    // by id so the chip click can just pass its id.
    $eventsPayload = [];
    foreach ($events as $event) {
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
            $actionHref = $event->external_url
                ?? 'mailto:hello@tuneupprecision.co.za?subject='.rawurlencode($event->displayTitle());
            $actionExternal = (bool) $event->external_url;
        } else {
            $actionLabel = $event->isFull() ? 'Join the waitlist' : 'Book this date';
            $actionHref = route('courses');
            $actionExternal = false;
        }

        $eventsPayload[$event->id] = [
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

    return view('calendar', [
        'month' => $month,
        'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
        'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
        'days' => $days,
        'eventsPayload' => $eventsPayload,
    ]);
})->name('calendar');

Route::get('/rifle-builder', RifleBuilder::class)->name('rifle-builder');
Route::get('/rifle-builder/{code}', RifleBuilder::class)->name('rifle-builder.share');

Route::get('/shop', function () {
    // Full product listing (out-of-stock / inactive items simply don't show).
    $products = Product::query()->available()->latest()->get();

    return view('shop', [
        'products' => $products,
    ]);
})->name('shop');

Route::get('/the-range', function () {
    // The video library. Videos are grouped by discipline (TrainingType); a
    // single featured video (if any) renders at the top. Members-only videos
    // are shown to guests as a locked placeholder and only actually play for
    // Dirk-verified members.
    $trainingTypes = TrainingType::query()->activeOrdered()->get();

    $videos = Video::query()
        ->with('trainingType')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderByDesc('id')
        ->get();

    $featured = $videos->firstWhere('is_featured', true);

    $videosByType = $videos->groupBy(fn (Video $v): string => $v->trainingType?->slug ?? 'other');

    return view('the-range', [
        'trainingTypes' => $trainingTypes,
        'videos' => $videos,
        'featured' => $featured,
        'videosByType' => $videosByType,
    ]);
})->name('range');

// Public authentication (member accounts). Admins (Dirk) also log in here —
// they get redirected to /admin on success.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/password/forgot', [PasswordController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/password/forgot', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/password/reset/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Newsletter subscribe (public form) + one-click unsubscribe.
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

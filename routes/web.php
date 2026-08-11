<?php

declare(strict_types=1);

use App\Enums\EventKind;
use App\Http\Controllers\NewsletterController;
use App\Models\CourseTemplate;
use App\Models\Product;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
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

Route::get('/calendar', function (Request $request) {
    // Full dated agenda, grouped by month, with the discipline filter chips
    // (previously the "Upcoming course dates" section on the landing page).
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

    return view('calendar', [
        'trainingTypes' => $trainingTypes,
        'selectedType' => $selectedType,
        'eventsByMonth' => $eventsByMonth,
    ]);
})->name('calendar');

Route::get('/courses', function () {
    // Public course offerings — one card per active CourseTemplate.
    $courses = CourseTemplate::query()
        ->with('trainingType')
        ->where('is_active', true)
        ->orderBy('title')
        ->get();

    return view('courses', [
        'courses' => $courses,
    ]);
})->name('courses');

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

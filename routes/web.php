<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    // Training-type filter chips (admin-managed disciplines: Reloading, PRS, ...).
    $trainingTypes = TrainingType::query()->activeOrdered()->get();
    $selectedType = $request->query('type');

    // Upcoming, publicly-visible dated events (published or full), grouped by
    // month for the agenda. Fully-booked dates DO display (as "Fully booked").
    $eventsByMonth = TrainingEvent::query()
        ->with('courseTemplate.trainingType')
        ->publiclyVisible()
        ->upcoming()
        ->when($selectedType, fn ($query, $slug) => $query->whereHas(
            'courseTemplate.trainingType',
            fn ($q) => $q->where('slug', $slug)
        ))
        ->get()
        ->groupBy(fn (TrainingEvent $event): string => $event->starts_on->format('F Y'));

    // Out-of-stock / inactive products simply don't display (no "sold out").
    $products = Product::query()->available()->latest()->take(4)->get();

    return view('home', [
        'trainingTypes' => $trainingTypes,
        'selectedType' => $selectedType,
        'eventsByMonth' => $eventsByMonth,
        'products' => $products,
    ]);
})->name('home');

// Public shop listing + product detail land in the next commit.

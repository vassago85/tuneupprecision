<?php

declare(strict_types=1);

use App\Models\CourseTemplate;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Active course templates with their next publicly-visible event (if any),
    // so the card can show the correct price and "Fully booked" state.
    $courses = CourseTemplate::query()
        ->where('is_active', true)
        ->with(['trainingEvents' => function ($query): void {
            $query->publiclyVisible()->upcoming();
        }])
        ->orderBy('base_price_cents')
        ->get();

    // Out-of-stock / inactive products simply don't display (no "sold out").
    $products = Product::query()->available()->latest()->take(4)->get();

    return view('home', [
        'courses' => $courses,
        'products' => $products,
    ]);
})->name('home');

// Public shop listing + product detail land in the next commit.

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\TrainingType;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            // The "What you'll learn" tabs are driven by the active training
            // types so Dirk can add/reorder disciplines and edit bullets from
            // the admin without touching Blade.
            'disciplineTypes' => TrainingType::activeList(),
            // Approved testimonials feed the homepage carousel (rendered only
            // when at least one exists). Random order so returning visitors
            // don't see the same one at the top every time.
            'testimonials' => Testimonial::query()
                ->approved()
                ->with(['trainingType', 'trainingEvent'])
                ->inRandomOrder()
                ->limit(8)
                ->get(),
        ]);
    }
}

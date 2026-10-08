<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TrainingType;
use App\Models\Video;
use Illuminate\Contracts\View\View;

/**
 * The video library. Videos are grouped by discipline (TrainingType); a
 * single featured video (if any) renders at the top. Members-only videos are
 * shown to guests as a locked placeholder and only actually play for
 * Dirk-verified members.
 */
class RangeController extends Controller
{
    public function __invoke(): View
    {
        $videos = Video::query()
            ->with(['trainingType', 'media'])
            ->activeOrdered()
            ->get();

        return view('the-range', [
            'trainingTypes' => TrainingType::activeList(),
            'videos' => $videos,
            'featured' => $videos->firstWhere('is_featured', true),
            'videosByType' => $videos->groupBy(fn (Video $v): string => $v->trainingType?->slug ?? 'other'),
        ]);
    }
}

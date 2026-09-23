<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Stable public URL Dirk can copy from the dashboard and send to shooters.
 * Submissions still land as pending until they are approved.
 */
class TestimonialLinkWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.testimonial-link';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'url' => route('testimonials.create'),
        ];
    }
}

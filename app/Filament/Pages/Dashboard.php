<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\TrainingEvents\TrainingEventResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;

class Dashboard extends BaseDashboard
{
    /**
     * @return int|array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 3];
    }

    protected function getHeaderActions(): array
    {
        $testimonialUrl = Js::from(route('testimonials.create'));

        return [
            // Shooters get this link to write a testimonial; it stays hidden until approved.
            Action::make('copyTestimonialLink')
                ->label('Copy testimonial link')
                ->icon(Heroicon::OutlinedLink)
                ->color('gray')
                // The clipboard API only exists on HTTPS; fall back to a prompt elsewhere.
                ->alpineClickHandler(<<<JS
                    const url = {$testimonialUrl};
                    (window.navigator.clipboard?.writeText(url) ?? Promise.reject())
                        .then(() => \$tooltip('Link copied', { theme: \$store.theme, timeout: 2000 }))
                        .catch(() => window.prompt('Copy this link and send it to the shooter:', url));
                    JS),
            Action::make('newEvent')
                ->label('Event')
                ->icon(Heroicon::OutlinedPlus)
                ->url(TrainingEventResource::getUrl('create')),
        ];
    }
}

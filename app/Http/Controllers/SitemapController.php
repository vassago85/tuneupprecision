<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EventKind;
use App\Models\TrainingEvent;
use Illuminate\Http\Response;

/**
 * Public sitemap.xml for Search Console and crawlers.
 *
 * Only indexable guest URLs are listed. Auth pages (/login, /register,
 * /password/*) and the admin panel stay out on purpose.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            $this->url(route('home'), 'weekly', '1.0'),
            $this->url(route('courses'), 'daily', '0.9'),
            $this->url(route('calendar'), 'daily', '0.8'),
            $this->url(route('range'), 'weekly', '0.7'),
            $this->url(route('shop'), 'weekly', '0.6'),
            $this->url(route('rifle-builder'), 'monthly', '0.6'),
            $this->url(route('contact.create'), 'monthly', '0.7'),
            $this->url(route('legal.privacy'), 'yearly', '0.3'),
            $this->url(route('legal.terms'), 'yearly', '0.3'),
            $this->url(route('llms'), 'weekly', '0.4'),
            $this->url(route('llms.full'), 'weekly', '0.3'),
        ];

        // Individual upcoming training dates so each date can appear in
        // search / rich results. Competitions Dirk is attending aren't
        // bookable pages on our site, so they're excluded.
        $events = TrainingEvent::query()
            ->with('courseTemplate.trainingType')
            ->where('kind', EventKind::Training->value)
            ->publiclyVisible()
            ->upcoming()
            ->get(['id', 'course_template_id', 'starts_on', 'updated_at']);

        foreach ($events as $event) {
            // We don't have per-event pages yet — link back to the courses
            // agenda with a hash so crawlers still discover the date.
            $urls[] = $this->url(
                route('courses').'#event-'.$event->id,
                'weekly',
                '0.6',
                $event->updated_at?->toAtomString(),
            );
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * @return array{loc: string, changefreq: string, priority: string, lastmod: ?string}
     */
    private function url(string $loc, string $changefreq, string $priority, ?string $lastmod = null): array
    {
        return [
            'loc' => $loc,
            'changefreq' => $changefreq,
            'priority' => $priority,
            'lastmod' => $lastmod,
        ];
    }
}

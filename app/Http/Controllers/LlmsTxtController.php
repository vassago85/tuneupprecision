<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EventKind;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use App\Support\Money;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * /llms.txt and /llms-full.txt — the llmstxt.org files language-model
 * crawlers look for. Public facts and URLs only; no member data.
 */
class LlmsTxtController extends Controller
{
    public function index(): Response
    {
        return $this->plain($this->indexBody());
    }

    public function full(): Response
    {
        return $this->plain($this->fullBody());
    }

    private function plain(string $body): Response
    {
        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function indexBody(): string
    {
        $lines = [
            '# Tune Up Precision — Long Range Rifle Training (South Africa)',
            '',
            '> Long range precision rifle training with Dirk Pio in South Africa. Full-day courses at a private facility cover foundation prone (zero, ballistics, first steel), applied long range past a kilometre, PRS positional shooting, and precision reloading. Small squads, your own rifle, private one-on-one coaching on request.',
            '',
            'Public HTML pages are canonical. This file is a curated map for language-model crawlers. A fuller dump (course details, upcoming dates and prices) is at [llms-full.txt]('.url('/llms-full.txt').'). XML sitemap: '.url('/sitemap.xml').'.',
            '',
            'AI crawlers are welcome on public pages. Do not use member dashboards, logins, admin URLs, or password reset endpoints as sources.',
            '',
            '## What you can book',
            '',
            '- [Courses]('.route('courses').'): Three disciplines — Reloading, PRS Shooting, Precision Long Range — with upcoming dates listed under each.',
            '- [Calendar]('.route('calendar').'): Month grid of every training date plus competitions Dirk is attending.',
        ];

        $types = TrainingType::query()
            ->activeOrdered()
            ->with(['courseTemplates' => fn ($q) => $q->where('is_active', true)])
            ->get();

        if ($types->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Disciplines';
            $lines[] = '';

            foreach ($types as $type) {
                $templateBlurbs = $type->courseTemplates
                    ->map(fn ($t) => $t->title)
                    ->filter()
                    ->implode(', ');
                $meta = $templateBlurbs ? ' — '.$templateBlurbs : '';
                $lines[] = '- **'.$type->name.'**'.$meta.($type->blurb ? ': '.$type->blurb : '');
            }
        }

        $matches = $this->publishedUpcomingCompetitions();

        if ($matches->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Upcoming competitions Dirk is attending';
            $lines[] = '';

            foreach ($matches as $match) {
                $meta = collect([
                    $match->starts_on?->format('Y-m-d'),
                    $match->disciplineName(),
                    $match->dirk_role,
                    $match->venue,
                ])->filter()->implode(' · ');

                $lines[] = '- '.$match->displayTitle().': '.$meta;
            }
        }

        $lines = array_merge($lines, [
            '',
            '## Other public pages',
            '',
            '- [Home]('.route('home').'): Positioning, values, next scheduled training date.',
            '- [The Range]('.route('range').'): Video library grouped by discipline.',
            '- [Shop]('.route('shop').'): Precision components, dies, brass, powder, projectiles.',
            '- [Rifle Builder]('.route('rifle-builder').'): Interactive rifle configurator with shareable specs.',
            '',
            '## Contact & legal',
            '',
            '- [Contact]('.route('contact.create').'): The only public way to reach Dirk — bookings, coaching, builds. No published email or phone.',
            '- [Privacy Policy]('.route('legal.privacy').') (POPIA).',
            '- [Terms]('.route('legal.terms').').',
            '',
            '## Optional',
            '',
            '- [llms-full.txt]('.url('/llms-full.txt').'): Every course template with price, duration, prerequisites, and every upcoming date.',
        ]);

        return implode("\n", $lines)."\n";
    }

    private function fullBody(): string
    {
        $sections = [$this->indexBody(), '---', ''];

        $types = TrainingType::query()
            ->activeOrdered()
            ->with([
                'courseTemplates' => fn ($q) => $q->where('is_active', true),
                'courseTemplates.trainingEvents' => fn ($q) => $q
                    ->where('kind', EventKind::Training->value)
                    ->publiclyVisible()
                    ->upcoming(),
            ])
            ->get();

        foreach ($types as $type) {
            $sections[] = '## '.$type->name;
            $sections[] = '';
            $sections[] = $type->blurb ?: '';
            $sections[] = '';

            foreach ($type->courseTemplates as $template) {
                $sections[] = '### '.$template->title;
                $sections[] = '';
                if ($template->level) {
                    $sections[] = '- Level: '.$template->level;
                }
                if ($template->base_price_cents) {
                    $sections[] = '- Base price: '.Money::format((int) $template->base_price_cents, false).' per shooter';
                }
                foreach ((array) $template->specs as $k => $v) {
                    $sections[] = '- '.$k.': '.$v;
                }
                if ($template->blurb) {
                    $sections[] = '';
                    $sections[] = $template->blurb;
                }

                $upcoming = $template->trainingEvents;
                if ($upcoming->isNotEmpty()) {
                    $sections[] = '';
                    $sections[] = '**Upcoming dates:**';
                    foreach ($upcoming as $event) {
                        $date = $event->starts_on?->format('D d M Y');
                        if ($event->ends_on && $event->ends_on->ne($event->starts_on)) {
                            $date = $event->starts_on->format('D d M').' – '.$event->ends_on->format('D d M Y');
                        }
                        $status = $event->isFull() ? 'Fully booked' : $event->seatsLeft().' of '.$event->capacity.' seats left';
                        $sections[] = '- '.$date.' · '.$status.($event->venue ? ' · '.$event->venue : '');
                    }
                }

                $sections[] = '';
            }

            $sections[] = '---';
            $sections[] = '';
        }

        return implode("\n", $sections)."\n";
    }

    /**
     * @return Collection<int, TrainingEvent>
     */
    private function publishedUpcomingCompetitions(): Collection
    {
        return TrainingEvent::query()
            ->with('trainingType')
            ->where('kind', EventKind::Competition->value)
            ->publiclyVisible()
            ->upcoming()
            ->limit(15)
            ->get();
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\TrainingEvents\TrainingEventResource;
use App\Models\TrainingEvent;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

class EventsCalendar extends Page
{
    protected string $view = 'filament.pages.events-calendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Calendar';

    protected static ?string $title = 'Events calendar';

    /** Current month as 'Y-m'. */
    public string $month = '';

    /** Active view: 'month' grid or 'agenda' list. */
    public string $calView = 'month';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function setCalView(string $view): void
    {
        $this->calView = in_array($view, ['month', 'agenda'], true) ? $view : 'month';
    }

    public function previousMonth(): void
    {
        $this->month = $this->cursor()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->cursor()->addMonthNoOverflow()->format('Y-m');
    }

    public function goToday(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function getMonthLabelProperty(): string
    {
        return $this->cursor()->format('F Y');
    }

    /**
     * A list of weeks, each a list of day cells for the current month grid.
     *
     * @return array<int, array<int, array{date: Carbon, inMonth: bool, isToday: bool, events: Collection}>>
     */
    public function getWeeksProperty(): array
    {
        $start = $this->cursor()->startOfMonth();
        $end = $this->cursor()->endOfMonth();

        $events = TrainingEvent::query()
            ->with('courseTemplate')
            ->whereBetween('starts_on', [$start->toDateString(), $end->toDateString()])
            ->orderBy('starts_on')
            ->get()
            ->groupBy(fn (TrainingEvent $event): string => $event->starts_on->format('Y-m-d'));

        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->startOfWeek(Carbon::MONDAY)->addDays(6);

        $weeks = [];
        $week = [];
        $day = $gridStart->copy();

        while ($day->lte($gridEnd)) {
            $week[] = [
                'date' => $day->copy(),
                'inMonth' => $day->month === $start->month,
                'isToday' => $day->isToday(),
                'events' => $events->get($day->format('Y-m-d'), collect()),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $day->addDay();
        }

        return $weeks;
    }

    /**
     * The current month's events as a flat, date-ordered list for agenda view.
     *
     * @return Collection<int, TrainingEvent>
     */
    public function getAgendaProperty(): Collection
    {
        return TrainingEvent::query()
            ->with('courseTemplate.trainingType')
            ->whereBetween('starts_on', [
                $this->cursor()->startOfMonth()->toDateString(),
                $this->cursor()->endOfMonth()->toDateString(),
            ])
            ->orderBy('starts_on')
            ->get();
    }

    public function eventUrl(int $eventId): string
    {
        return TrainingEventResource::getUrl('edit', ['record' => $eventId]);
    }

    public function createUrl(): string
    {
        return TrainingEventResource::getUrl('create');
    }

    protected function cursor(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
    }
}

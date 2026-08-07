<x-filament::page>
    {{--
        Self-contained calendar styling. The admin panel has no custom Filament
        theme, so raw Tailwind utilities in custom Blade views aren't compiled.
        Rather than add a whole theme build for one page, this view ships its own
        scoped CSS (prefixed .tu-cal-*) — the same hand-written approach the public
        site uses. Dark mode is handled via Filament's `.dark` root class.
    --}}
    <style>
        .tu-cal-bar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
        .tu-cal-bar .grp{display:flex;align-items:center;gap:.5rem}
        .tu-cal-title{font-size:1.25rem;font-weight:600;line-height:1.2;color:rgb(3 7 18)}
        .dark .tu-cal-title{color:#fff}

        .tu-cal{margin-top:1rem;overflow:hidden;border-radius:.75rem;box-shadow:0 0 0 1px rgba(3,7,18,.05)}
        .dark .tu-cal{box-shadow:0 0 0 1px rgba(255,255,255,.1)}

        .tu-cal-week,.tu-cal-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr))}
        .tu-cal-week{background:#f9fafb}
        .dark .tu-cal-week{background:rgba(255,255,255,.05)}
        .tu-cal-week .wd{padding:.5rem;text-align:center;font-size:.75rem;font-weight:500;text-transform:uppercase;letter-spacing:.05em;color:#6b7280}
        .dark .tu-cal-week .wd{color:#9ca3af}

        .tu-cal-cell{min-height:7rem;border-top:1px solid rgba(3,7,18,.05);border-left:1px solid rgba(3,7,18,.05);padding:.375rem}
        .dark .tu-cal-cell{border-color:rgba(255,255,255,.1)}
        .tu-cal-cell:nth-child(7n+1){border-left:0}
        .tu-cal-cell.is-in{background:#fff}
        .dark .tu-cal-cell.is-in{background:#111827}
        .tu-cal-cell.is-out{background:rgba(249,250,251,.7)}
        .dark .tu-cal-cell.is-out{background:rgba(255,255,255,.05)}

        .tu-cal-day{margin-bottom:.25rem;display:flex;height:1.5rem;width:1.5rem;align-items:center;justify-content:center;border-radius:9999px;font-size:.75rem;line-height:1}
        .tu-cal-day.is-in{font-weight:600;color:rgb(3 7 18)}
        .dark .tu-cal-day.is-in{color:#fff}
        .tu-cal-day.is-out{color:#9ca3af}
        .dark .tu-cal-day.is-out{color:#4b5563}
        .tu-cal-day.is-today{background:#D45B2E;color:#fff}

        .tu-cal-events{display:flex;flex-direction:column;gap:.25rem}
        .tu-cal-event{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border-radius:.375rem;padding:.25rem .375rem;font-size:.75rem;line-height:1.2}
        .tu-cal-event .cap{opacity:.7}

        .tu-cal-note{margin-top:.75rem;font-size:.75rem;color:#6b7280}
        .dark .tu-cal-note{color:#9ca3af}

        /* status pills */
        .tu-st-published{background:rgba(16,185,129,.15);color:#047857;box-shadow:inset 0 0 0 1px rgba(16,185,129,.3)}
        .dark .tu-st-published{color:#6ee7b7}
        .tu-st-full{background:rgba(245,158,11,.15);color:#b45309;box-shadow:inset 0 0 0 1px rgba(245,158,11,.3)}
        .dark .tu-st-full{color:#fcd34d}
        .tu-st-draft{background:rgba(107,114,128,.15);color:#4b5563;box-shadow:inset 0 0 0 1px rgba(107,114,128,.3)}
        .dark .tu-st-draft{color:#d1d5db}
        .tu-st-cancelled{background:rgba(239,68,68,.15);color:#b91c1c;box-shadow:inset 0 0 0 1px rgba(239,68,68,.3)}
        .dark .tu-st-cancelled{color:#fca5a5}
        .tu-st-completed{background:rgba(14,165,233,.15);color:#0369a1;box-shadow:inset 0 0 0 1px rgba(14,165,233,.3)}
        .dark .tu-st-completed{color:#7dd3fc}
    </style>

    @php
        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $statusClasses = [
            'published' => 'tu-st-published',
            'full'      => 'tu-st-full',
            'draft'     => 'tu-st-draft',
            'cancelled' => 'tu-st-cancelled',
            'completed' => 'tu-st-completed',
        ];
    @endphp

    <div class="tu-cal-bar">
        <div class="grp">
            <x-filament::button color="gray" size="sm" icon="heroicon-m-chevron-left" wire:click="previousMonth">
                Prev
            </x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="goToday">
                Today
            </x-filament::button>
            <x-filament::button color="gray" size="sm" icon="heroicon-m-chevron-right" icon-position="after" wire:click="nextMonth">
                Next
            </x-filament::button>
        </div>

        <h2 class="tu-cal-title">{{ $this->monthLabel }}</h2>

        <x-filament::button tag="a" href="{{ $this->createUrl() }}" size="sm" icon="heroicon-m-plus">
            New event
        </x-filament::button>
    </div>

    <div class="tu-cal">
        {{-- Weekday header --}}
        <div class="tu-cal-week">
            @foreach ($weekdays as $day)
                <div class="wd">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Day cells --}}
        <div class="tu-cal-grid">
            @foreach ($this->weeks as $week)
                @foreach ($week as $cell)
                    <div class="tu-cal-cell {{ $cell['inMonth'] ? 'is-in' : 'is-out' }}">
                        <div @class([
                            'tu-cal-day',
                            'is-in' => $cell['inMonth'],
                            'is-out' => ! $cell['inMonth'],
                            'is-today' => $cell['isToday'],
                        ])>
                            {{ $cell['date']->format('j') }}
                        </div>

                        <div class="tu-cal-events">
                            @foreach ($cell['events'] as $event)
                                <a href="{{ $this->eventUrl($event->id) }}"
                                   class="tu-cal-event {{ $statusClasses[$event->status->value] ?? $statusClasses['draft'] }}"
                                   title="{{ $event->courseTemplate?->title }} — {{ $event->seatsLeft() }}/{{ $event->capacity }} seats left ({{ $event->status->getLabel() }})">
                                    {{ $event->courseTemplate?->title ?? 'Event' }}
                                    <span class="cap">· {{ $event->seatsLeft() }}/{{ $event->capacity }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <p class="tu-cal-note">
        Click an event to edit it, or use <span style="font-weight:500">New event</span> to schedule a date.
    </p>
</x-filament::page>

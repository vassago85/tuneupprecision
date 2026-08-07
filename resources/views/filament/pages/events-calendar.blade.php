<x-filament::page>
    {{--
        Self-contained calendar styling using the admin design tokens (--tu-*)
        defined in public/css/admin-theme.css. Dark mode via Filament's `.dark`.
    --}}
    <style>
        .tu-cal-bar{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
        .tu-cal-bar .spacer{flex:1}
        .tu-cal-nav{display:flex;align-items:center;gap:.35rem}
        .tu-cal-title{font-size:1.05rem;font-weight:600;line-height:1.2;color:var(--tu-text);min-width:9.5rem;text-align:center}

        /* segmented Month | Agenda control */
        .tu-seg{display:inline-flex;background:var(--tu-surface-2);border:1px solid var(--tu-border);border-radius:8px;padding:2px}
        .tu-seg button{appearance:none;border:0;background:transparent;cursor:pointer;font-size:.8rem;font-weight:600;color:var(--tu-text-2);padding:.3rem .7rem;border-radius:6px;line-height:1}
        .tu-seg button.is-active{background:var(--tu-surface);color:var(--tu-text);box-shadow:var(--tu-shadow)}

        .tu-cal{margin-top:1rem;overflow:hidden;border-radius:var(--tu-radius);border:1px solid var(--tu-border);background:var(--tu-surface);box-shadow:var(--tu-shadow)}
        .tu-cal-week,.tu-cal-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr))}
        .tu-cal-week{background:var(--tu-surface-2)}
        .tu-cal-week .wd{padding:.4rem;text-align:center;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--tu-text-2)}

        .tu-cal-cell{min-height:5.5rem;border-top:1px solid var(--tu-border-soft);border-left:1px solid var(--tu-border-soft);padding:.35rem}
        .tu-cal-cell:nth-child(7n+1){border-left:0}
        .tu-cal-cell.is-out{background:color-mix(in srgb, var(--tu-surface-2) 55%, transparent)}
        .tu-cal-day{margin-bottom:.25rem;display:flex;height:1.4rem;width:1.4rem;align-items:center;justify-content:center;border-radius:9999px;font-size:.72rem;line-height:1}
        .tu-cal-day.is-in{font-weight:600;color:var(--tu-text)}
        .tu-cal-day.is-out{color:var(--tu-text-2);opacity:.6}
        .tu-cal-day.is-today{background:var(--tu-orange);color:#fff}

        .tu-cal-events{display:flex;flex-direction:column;gap:.2rem}
        .tu-cal-event{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border-radius:6px;padding:.2rem .4rem;font-size:.72rem;line-height:1.25}
        .tu-cal-event .cap{opacity:.75;font-variant-numeric:tabular-nums}

        /* agenda list */
        .tu-agenda{margin-top:1rem;border:1px solid var(--tu-border);border-radius:var(--tu-radius);background:var(--tu-surface);box-shadow:var(--tu-shadow);overflow:hidden}
        .tu-agenda-row{display:flex;align-items:center;gap:1rem;padding:.7rem .9rem;border-top:1px solid var(--tu-border-soft)}
        .tu-agenda-row:first-child{border-top:0}
        .tu-agenda-row:hover{background:var(--tu-surface-2)}
        .tu-agenda-date{flex:none;width:3.4rem;text-align:center;line-height:1.05}
        .tu-agenda-date .d{font-size:1.15rem;font-weight:700;color:var(--tu-text)}
        .tu-agenda-date .m{font-size:.66rem;text-transform:uppercase;letter-spacing:.06em;color:var(--tu-text-2)}
        .tu-agenda-main{flex:1;min-width:0}
        .tu-agenda-main .t{font-weight:600;color:var(--tu-text)}
        .tu-agenda-main .s{font-size:.78rem;color:var(--tu-text-2)}
        .tu-agenda-cap{flex:none;font-size:.78rem;font-variant-numeric:tabular-nums;color:var(--tu-text-2)}
        .tu-agenda-empty{padding:2rem;text-align:center;color:var(--tu-text-2);font-size:.85rem}

        /* status pills (shared) */
        .tu-st-published{background:rgba(27,175,122,.14);color:#0f8f61}
        .tu-st-full{background:rgba(181,121,11,.16);color:#8a5c07}
        .tu-st-draft{background:rgba(102,112,133,.16);color:#556}
        .tu-st-cancelled{background:rgba(201,67,63,.14);color:#a5322e}
        .tu-st-completed{background:rgba(42,120,214,.14);color:#1f5fb0}
        .tu-pill{display:inline-block;border-radius:6px;padding:.15rem .5rem;font-size:.7rem;font-weight:600}
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
        <div class="tu-cal-nav">
            <x-filament::button color="gray" size="sm" icon="heroicon-m-chevron-left" icon-alias="prev" wire:click="previousMonth" />
            <span class="tu-cal-title">{{ $this->monthLabel }}</span>
            <x-filament::button color="gray" size="sm" icon="heroicon-m-chevron-right" wire:click="nextMonth" />
            <x-filament::button color="gray" size="sm" wire:click="goToday">Today</x-filament::button>
        </div>

        <div class="spacer"></div>

        <div class="tu-seg">
            <button type="button" wire:click="setCalView('month')" class="{{ $calView === 'month' ? 'is-active' : '' }}">Month</button>
            <button type="button" wire:click="setCalView('agenda')" class="{{ $calView === 'agenda' ? 'is-active' : '' }}">Agenda</button>
        </div>

        <x-filament::button tag="a" href="{{ $this->createUrl() }}" size="sm" icon="heroicon-m-plus">
            Event
        </x-filament::button>
    </div>

    @if ($calView === 'month')
        <div class="tu-cal">
            <div class="tu-cal-week">
                @foreach ($weekdays as $day)
                    <div class="wd">{{ $day }}</div>
                @endforeach
            </div>

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
                                        <span class="cap">· {{ $event->seats_taken }}/{{ $event->capacity }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @else
        <div class="tu-agenda">
            @forelse ($this->agenda as $event)
                <a href="{{ $this->eventUrl($event->id) }}" class="tu-agenda-row">
                    <div class="tu-agenda-date">
                        <div class="d">{{ $event->starts_on->format('d') }}</div>
                        <div class="m">{{ $event->starts_on->format('M') }}</div>
                    </div>
                    <div class="tu-agenda-main">
                        <div class="t">{{ $event->courseTemplate?->title ?? 'Event' }}</div>
                        <div class="s">
                            {{ $event->courseTemplate?->trainingType?->name }}
                            @if ($event->venue) · {{ $event->venue }} @endif
                        </div>
                    </div>
                    <div class="tu-agenda-cap">{{ $event->seats_taken }} / {{ $event->capacity }}</div>
                    <span class="tu-pill {{ $statusClasses[$event->status->value] ?? $statusClasses['draft'] }}">
                        {{ $event->status->getLabel() }}
                    </span>
                </a>
            @empty
                <div class="tu-agenda-empty">No events scheduled for {{ $this->monthLabel }}.</div>
            @endforelse
        </div>
    @endif
</x-filament::page>

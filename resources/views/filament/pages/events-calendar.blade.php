<x-filament::page>
    @php
        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $statusClasses = [
            'published' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-500/30',
            'full'      => 'bg-amber-500/15 text-amber-700 dark:text-amber-300 ring-1 ring-amber-500/30',
            'draft'     => 'bg-gray-500/15 text-gray-600 dark:text-gray-300 ring-1 ring-gray-500/30',
            'cancelled' => 'bg-red-500/15 text-red-700 dark:text-red-300 ring-1 ring-red-500/30',
            'completed' => 'bg-sky-500/15 text-sky-700 dark:text-sky-300 ring-1 ring-sky-500/30',
        ];
    @endphp

    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
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

        <h2 class="text-xl font-semibold text-gray-950 dark:text-white">{{ $this->monthLabel }}</h2>

        <x-filament::button tag="a" href="{{ $this->createUrl() }}" size="sm" icon="heroicon-m-plus">
            New event
        </x-filament::button>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
        {{-- Weekday header --}}
        <div class="grid grid-cols-7 bg-gray-50 dark:bg-white/5">
            @foreach ($weekdays as $day)
                <div class="px-2 py-2 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Day cells --}}
        <div class="grid grid-cols-7">
            @foreach ($this->weeks as $week)
                @foreach ($week as $cell)
                    <div @class([
                        'min-h-28 border-t border-l border-gray-950/5 dark:border-white/10 p-1.5 first:border-l-0',
                        'bg-white dark:bg-gray-900' => $cell['inMonth'],
                        'bg-gray-50/70 dark:bg-white/5' => ! $cell['inMonth'],
                    ])>
                        <div @class([
                            'mb-1 flex h-6 w-6 items-center justify-center rounded-full text-xs',
                            'font-semibold text-gray-950 dark:text-white' => $cell['inMonth'],
                            'text-gray-400 dark:text-gray-600' => ! $cell['inMonth'],
                            'bg-primary-500 text-white dark:text-white' => $cell['isToday'],
                        ])>
                            {{ $cell['date']->format('j') }}
                        </div>

                        <div class="space-y-1">
                            @foreach ($cell['events'] as $event)
                                <a href="{{ $this->eventUrl($event->id) }}"
                                   class="block truncate rounded-md px-1.5 py-1 text-xs {{ $statusClasses[$event->status->value] ?? $statusClasses['draft'] }}"
                                   title="{{ $event->courseTemplate?->title }} — {{ $event->seatsLeft() }}/{{ $event->capacity }} seats left ({{ $event->status->getLabel() }})">
                                    {{ $event->courseTemplate?->title ?? 'Event' }}
                                    <span class="opacity-70">· {{ $event->seatsLeft() }}/{{ $event->capacity }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        Click an event to edit it, or use <span class="font-medium">New event</span> to schedule a date.
    </p>
</x-filament::page>

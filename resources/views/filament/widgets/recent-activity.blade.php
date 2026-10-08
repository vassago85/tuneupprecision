<x-filament-widgets::widget>
    <x-filament::section heading="Recent bookings and orders">
        @if ($entries->isEmpty())
            <p class="tu-feed-empty">No bookings or orders yet.</p>
        @else
            <ul class="tu-feed">
                @foreach ($entries as $entry)
                    <li>
                        <a href="{{ $entry['url'] }}" class="tu-feed-item">
                            <span class="tu-feed-main">
                                <span class="tu-feed-name">{{ $entry['name'] }}</span>
                                <span class="tu-feed-what">{{ $entry['what'] }} · {{ $entry['at']?->diffForHumans() }}</span>
                            </span>
                            <span class="tu-feed-side">
                                <span class="tu-feed-amount">{{ $entry['amount'] }}</span>
                                <x-filament::badge :color="$entry['status']->getColor()" size="sm">
                                    {{ $entry['status']->getLabel() }}
                                </x-filament::badge>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

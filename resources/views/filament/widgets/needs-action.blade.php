<x-filament-widgets::widget>
    @if ($items === [])
        <div class="tu-caught-up">
            <x-filament::icon icon="heroicon-m-check-circle" class="tu-caught-up-icon" />
            <span>All caught up. Nothing is waiting on you.</span>
        </div>
    @else
        <div class="tu-actions">
            @foreach ($items as $item)
                <a href="{{ $item['url'] }}" class="tu-action">
                    <span class="tu-action-count">{{ $item['count'] }}</span>
                    <span class="tu-action-text">
                        <span class="tu-action-label">{{ $item['label'] }}</span>
                        @if ($item['detail'])
                            <span class="tu-action-detail">{{ $item['detail'] }}</span>
                        @endif
                    </span>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="tu-action-chevron" />
                </a>
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>

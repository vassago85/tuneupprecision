<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">EFT bank details</x-slot>
        <x-slot name="description">Where guests pay for bookings and orders. Sourced from the environment for now.</x-slot>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($this->getEft() as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ \Illuminate\Support\Str::headline($key) }}
                    </dt>
                    <dd class="mt-1 font-mono text-sm text-gray-950 dark:text-white">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Reference formats</x-slot>
        <x-slot name="description">Generated automatically on every booking and order.</x-slot>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($this->getReferences() as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ \Illuminate\Support\Str::headline($key) }}
                    </dt>
                    <dd class="mt-1 font-mono text-sm text-gray-950 dark:text-white">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Coming next</x-slot>
        {{-- TODO: Make these editable + persisted in a later commit (settings store),
             and surface the EFT block on the guest checkout screen. --}}
        <p class="text-sm text-gray-500 dark:text-gray-400">
            This is a read-only stub. Editing &amp; persistence, plus showing these
            details on the guest checkout screen, land in a later commit.
        </p>
    </x-filament::section>
</x-filament::page>

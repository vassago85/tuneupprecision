<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top:1.5rem; display:flex; gap:0.75rem; flex-wrap:wrap">
            <x-filament::button type="submit">
                Save changes
            </x-filament::button>

            {{ $this->sendTestAction }}
        </div>
    </form>
</x-filament::page>

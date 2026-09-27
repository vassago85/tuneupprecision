<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top:1.5rem;display:flex;gap:.75rem;align-items:center">
            <x-filament::button type="submit">
                Save answers
            </x-filament::button>
            <span style="font-size:.85rem;color:var(--tu-text-2,#667085)">
                A copy is emailed to Paul on save. You can come back and change anything later.
            </span>
        </div>
    </form>
</x-filament::page>

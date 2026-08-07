<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top:1.5rem">
            <x-filament::button type="submit">
                Save changes
            </x-filament::button>
        </div>
    </form>

    <x-filament::section>
        <x-slot name="heading">Reference formats</x-slot>
        <x-slot name="description">Read-only — generated automatically on every booking and order.</x-slot>

        <style>
            .tu-ref-list{display:flex;flex-direction:column}
            .tu-ref-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.6rem 0;border-top:1px solid var(--tu-border-soft,#ECEEF1)}
            .tu-ref-row:first-child{border-top:0}
            .tu-ref-label{font-size:.85rem;font-weight:500;color:var(--tu-text,#0B2239)}
            .tu-ref-label .hint{display:block;font-size:.72rem;font-weight:400;color:var(--tu-text-2,#667085)}
            .tu-ref-example{font-family:var(--tu-mono,ui-monospace,monospace);font-size:.8rem;color:var(--tu-text-2,#667085);background:var(--tu-surface-2,#F0EFEC);border:1px solid var(--tu-border,#E2E5E9);border-radius:6px;padding:.25rem .5rem;white-space:nowrap}
        </style>

        <div class="tu-ref-list">
            @foreach ($this->getReferences() as $key => $value)
                <div class="tu-ref-row">
                    <div class="tu-ref-label">
                        {{ \Illuminate\Support\Str::headline($key) }} reference
                        <span class="hint">Pattern · # is a digit</span>
                    </div>
                    <code class="tu-ref-example">{{ $value }}</code>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::page>

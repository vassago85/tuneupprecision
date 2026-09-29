<x-filament::page>
    @php
        $failures = $this->getFailures();
        $passed = $failures === [];
    @endphp

    <x-filament::section>
        <x-slot name="heading">legal:check</x-slot>
        <x-slot name="description">Production deploys fail if any required value is empty or still a placeholder (zeros, +27 00, and similar).</x-slot>

        @if ($passed)
            <p style="margin:0;font-weight:600;color:#1BAF7A">Passed — every required key has a real value.</p>
        @else
            <p style="margin:0 0 8px;font-weight:600;color:#C9433F">Failed — these keys are empty or still placeholders:</p>
            <ul style="margin:0;padding-left:1.2rem">
                @foreach ($failures as $key)
                    <li><code>{{ $key }}</code></li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>

    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top:1.5rem">
            <x-filament::button type="submit">
                Save identity
            </x-filament::button>
        </div>
    </form>

    <x-filament::section>
        <x-slot name="heading">Document last updated</x-slot>
        <x-slot name="description">Dates live in config/legal.php. Legal prose is version-controlled — it is not edited here.</x-slot>

        <div class="tu-ref-list">
            @foreach ($this->getUpdated() as $doc => $date)
                <div class="tu-ref-row">
                    <div class="tu-ref-label">{{ \Illuminate\Support\Str::headline($doc) }}</div>
                    <code class="tu-ref-example">{{ $date }}</code>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <style>
        .tu-ref-list{display:flex;flex-direction:column}
        .tu-ref-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.6rem 0;border-top:1px solid var(--tu-border-soft,#ECEEF1)}
        .tu-ref-row:first-child{border-top:0}
        .tu-ref-label{font-size:.85rem;font-weight:500;color:var(--tu-text,#0B2239)}
        .tu-ref-example{font-family:var(--tu-mono,ui-monospace,monospace);font-size:.8rem;color:var(--tu-text-2,#667085);background:var(--tu-surface-2,#F0EFEC);border:1px solid var(--tu-border,#E2E5E9);border-radius:6px;padding:.25rem .5rem}
    </style>
</x-filament::page>

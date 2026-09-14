<x-filament::page>
    @php
        $identity = $this->getIdentity();
        $failures = $this->getFailures();
        $passed = $failures === [];
        $displayKeys = [
            'legal_name' => 'Legal name',
            'trading_as' => 'Trading as',
            'legal_status' => 'Legal status',
            'registration_no' => 'Registration number',
            'vat_no' => 'VAT number',
            'dealer_licence_no' => 'Dealer licence',
            'office_bearers' => 'Office bearers',
            'physical_address' => 'Physical address',
            'postal_address' => 'Postal address',
            'legal_email' => 'Email',
            'legal_phone' => 'Telephone',
        ];
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

    <x-filament::section>
        <x-slot name="heading">Effective identity</x-slot>
        <x-slot name="description">Statutory fields come from .env (LEGAL_*). Telephone, email, VAT and dealer licence can also be changed on Settings without a deploy. Empty lines are omitted on the public site.</x-slot>

        <div class="tu-ref-list">
            @foreach ($displayKeys as $key => $label)
                <div class="tu-ref-row">
                    <div class="tu-ref-label">
                        {{ $label }}
                        <span class="hint">{{ $key }}</span>
                    </div>
                    <code class="tu-ref-example">{{ $identity[$key] ?: '—' }}</code>
                </div>
            @endforeach
        </div>
    </x-filament::section>

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
        .tu-ref-label .hint{display:block;font-size:.72rem;font-weight:400;color:var(--tu-text-2,#667085)}
        .tu-ref-example{font-family:var(--tu-mono,ui-monospace,monospace);font-size:.8rem;color:var(--tu-text-2,#667085);background:var(--tu-surface-2,#F0EFEC);border:1px solid var(--tu-border,#E2E5E9);border-radius:6px;padding:.25rem .5rem}
    </style>
</x-filament::page>

@php
    $dealer = $dealer ?? false;
    $costCents = $costCents ?? 0;
    $gp = ($priceCents ?? 0) > 0 ? number_format((($priceCents - $costCents) / $priceCents) * 100, 0) : '0';
@endphp
<div
    class="opt{{ !empty($selected) ? ' sel' : '' }}{{ !empty($disabled) ? ' disabled' : '' }}"
    role="button"
    tabindex="{{ !empty($disabled) ? '-1' : '0' }}"
    aria-pressed="{{ !empty($selected) ? 'true' : 'false' }}"
    @if (!empty($click) && empty($disabled))
        wire:click="{{ $click }}"
        x-on:keydown.enter.prevent="$wire.{{ $click }}"
        x-on:keydown.space.prevent="$wire.{{ $click }}"
    @endif
>
    <span class="tick">&#10003;</span>
    @if (($priceCents ?? 0) === 0)
        <span class="badge">NO CHARGE</span>
    @endif
    <div class="thumb">
        @if (!empty($image))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($image) }}" alt="{{ $brand }} {{ $name }}">
        @else
            <x-rifle-builder.art :key="$art" />
        @endif
    </div>
    <div class="bd">
        <div class="brand">{{ $brand }}</div>
        <div class="nm">{{ $name }}</div>
        <div class="sp">{!! implode('<br>', array_map('e', $specs ?? [])) !!}</div>
        @if (!empty($note))
            <div class="fitwarn">{{ $note }}</div>
        @endif
        <div class="pr">
            @if (($priceCents ?? 0) === 0)
                INCLUDED
            @else
                {{ \App\Support\Money::format((int) $priceCents, false) }} <small>INCL VAT</small>
            @endif
            @if ($dealer)
                <div class="costline">COST {{ \App\Support\Money::format((int) $costCents, false) }} · GP {{ $gp }}%</div>
            @endif
        </div>
        @if (!empty($qty))
            <div class="qty" wire:click.stop>
                <button type="button" wire:click="setQuantity({{ $qtyId }}, {{ max(1, ($qtyValue ?? 1) - 1) }})">&minus;</button>
                <input type="number" min="1" value="{{ $qtyValue ?? 1 }}" wire:change="setQuantity({{ $qtyId }}, $event.target.value)" aria-label="Quantity">
                <button type="button" wire:click="setQuantity({{ $qtyId }}, {{ ($qtyValue ?? 1) + 1 }})">+</button>
            </div>
        @endif
    </div>
</div>

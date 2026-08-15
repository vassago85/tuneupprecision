@php
    $result = $this->result;
    $steps = $this->steps;
    $catalogue = $this->catalogue;
    $n = 0;
@endphp

<div class="rb-picker" wire:key="picker-{{ $platform }}">
    @include('partials.rifle-builder-styles')
    @foreach ($steps as $step)
        @php
            $n++;
            $open = in_array($step['id'], $openSteps, true);
            $items = $step['type'] === 'platform' ? collect() : ($catalogue[$step['id']] ?? collect());
            $pick = '';
            $done = false;
            if ($step['type'] === 'platform') {
                $pick = $platform === 'barrelled' ? 'Barrelled action' : 'Separate action + barrel';
                $done = true;
            } elseif ($step['type'] === 'multi') {
                $count = count($multis[$step['id']] ?? []);
                $pick = $count ? $count.' selected' : ($step['optional'] ? 'None' : '—');
                $done = $count > 0;
            } else {
                $id = $singles[$step['id']] ?? null;
                $chosen = $id ? $items->firstWhere('id', $id) : null;
                $pick = $chosen ? $chosen->brand.' '.$chosen->name : ($step['optional'] ? 'Not added' : 'Select one');
                $done = (bool) $chosen;
            }
        @endphp
        <div class="step{{ $done ? ' done' : '' }}{{ $open ? ' open' : '' }}" wire:key="step-{{ $step['id'] }}">
            <button type="button" class="step-hd" wire:click="toggleStep('{{ $step['id'] }}')" aria-expanded="{{ $open ? 'true' : 'false' }}">
                <span class="step-no mono">{{ $n }}</span>
                <span class="step-ti">{{ $step['title'] }}@if ($step['optional'])<span class="opt">Optional</span>@endif</span>
                <span class="step-pick">{{ $pick }}</span>
                <svg class="chev" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 6 L8 11 L13 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            @if ($open)
            <div class="step-bd">
                @if ($step['hint'])
                    <p class="hint">{{ $step['hint'] }}</p>
                @endif

                @if ($step['type'] === 'platform')
                    <div class="opts">
                        @include('livewire.partials.rifle-option-card', [
                            'brand' => 'Route A',
                            'name' => 'Barrelled Action',
                            'specs' => ['Action + barrel as one unit', 'Already headspaced', 'Shortest lead time'],
                            'priceCents' => 0,
                            'selected' => $platform === 'barrelled',
                            'disabled' => false,
                            'note' => null,
                            'art' => 'barrelled',
                            'image' => null,
                            'qty' => false,
                            'click' => "setPlatform('barrelled')",
                        ])
                        @include('livewire.partials.rifle-option-card', [
                            'brand' => 'Route B',
                            'name' => 'Separate Action + Barrel',
                            'specs' => ['Choose action and blank', 'Chambered to your spec', 'Full custom control'],
                            'priceCents' => 0,
                            'selected' => $platform === 'separate',
                            'disabled' => false,
                            'note' => null,
                            'art' => 'action',
                            'image' => null,
                            'qty' => false,
                            'click' => "setPlatform('separate')",
                        ])
                    </div>
                @else
                    <div class="opts">
                        @if ($step['id'] === 'trigger' && $result->needsTriggerChoice)
                            <div class="fitwarn" role="alert">
                                This action requires an aftermarket trigger. Pick one below to continue.
                            </div>
                        @endif
                        @foreach ($items as $item)
                            @continue($step['id'] === 'trigger' && $result->requiresAftermarketTrigger && $item->is_factory_option)
                            @php
                                $selected = $step['type'] === 'multi'
                                    ? in_array($item->id, $multis[$step['id']] ?? [], true)
                                    : (($singles[$step['id']] ?? null) === $item->id);
                                $note = $result->disabledReasons[$item->id] ?? null;
                                $click = $note
                                    ? null
                                    : ($step['type'] === 'multi'
                                        ? "toggleMulti('{$step['id']}', {$item->id})"
                                        : "selectSingle('{$step['id']}', {$item->id})");
                            @endphp
                            @include('livewire.partials.rifle-option-card', [
                                'brand' => $item->brand,
                                'name' => $item->name,
                                'specs' => $item->specs ?? [],
                                'priceCents' => $item->price_cents,
                                'costCents' => $item->cost_cents,
                                'selected' => $selected,
                                'disabled' => (bool) $note,
                                'note' => $note,
                                'art' => $step['id'],
                                'image' => $item->image_path,
                                'qty' => $selected && $item->allows_quantity,
                                'qtyValue' => $quantities[$item->id] ?? 1,
                                'qtyId' => $item->id,
                                'click' => $click,
                                'dealer' => $dealerMode,
                            ])
                        @endforeach
                    </div>
                @endif

                @if (($step['config'] ?? null) === 'barrel')
                    <div class="cfg">
                        <div>
                            <label for="rb-chambering">Chambering</label>
                            <select id="rb-chambering" wire:model.live="chambering">
                                @foreach (config('tuneup.rifle_builder.chamberings') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rb-length">Finished length</label>
                            <select id="rb-length" wire:model.live="barrelLength">
                                @foreach (config('tuneup.rifle_builder.barrel_lengths') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rb-twist">Twist rate</label>
                            <select id="rb-twist" wire:model.live="barrelTwist">
                                @foreach (config('tuneup.rifle_builder.twists') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rb-finish">Finish</label>
                            <select id="rb-finish" wire:model.live="barrelFinish">
                                @foreach (config('tuneup.rifle_builder.finishes') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>
            @endif
        </div>
    @endforeach
</div>

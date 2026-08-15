<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuoteStatus;
use App\Enums\RiflePlatform;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Quote;
use App\RifleBuilder\BuildLine;
use App\RifleBuilder\BuildResult;
use App\RifleBuilder\BuildSelection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RifleBuildService
{
    public function evaluate(BuildSelection $selection): BuildResult
    {
        $components = $this->loadAvailable()->keyBy('id');
        $selection = $this->clearIncompatible($selection, $components);

        $footprint = $this->footprint($selection, $components);
        $opticTube = $this->opticTube($selection, $components);
        $triggerRequired = $this->requiresAftermarketTrigger($selection, $components);
        $disabledReasons = $this->disabledReasons($components, $footprint, $opticTube, $triggerRequired);
        $needsTriggerChoice = $triggerRequired && ! $this->hasAftermarketTrigger($selection, $components);

        $lines = $this->buildLines($selection, $components);
        $lines = $this->appendLabour($selection, $components, $lines);

        $subtotal = array_sum(array_map(fn (BuildLine $line): int => $line->lineTotalCents, $lines));
        $cost = array_sum(array_map(fn (BuildLine $line): int => $line->lineCostCents, $lines));

        $discount = $this->discountCents($subtotal, $selection);
        $total = max(0, $subtotal - $discount);
        $exVat = (int) round($total / (1 + (float) config('tuneup.rifle_builder.vat_rate', 0.15)));
        $vat = $total - $exVat;
        $gp = $total - $cost;
        $margin = $total > 0 ? ($gp / $total) * 100 : 0.0;
        $deposit = (int) round($total * max(0, $selection->depositPercent) / 100);

        return new BuildResult(
            selection: $selection,
            lines: $lines,
            subtotalCents: $subtotal,
            discountCents: $discount,
            totalCents: $total,
            exVatCents: $exVat,
            vatCents: $vat,
            costCents: $cost,
            grossProfitCents: $gp,
            marginPercent: $margin,
            depositCents: $deposit,
            leadTime: $this->leadTime($selection, $components),
            footprint: $footprint,
            opticTube: $opticTube,
            disabledReasons: $disabledReasons,
            requiresAftermarketTrigger: $triggerRequired,
            needsTriggerChoice: $needsTriggerChoice,
        );
    }

    /**
     * Persist a new quote with snapshot lines. Catalogue edits after this
     * point cannot change the stored amounts.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function persistQuote(BuildSelection $selection, array $attributes): Quote
    {
        $result = $this->evaluate($selection);

        return DB::transaction(function () use ($result, $attributes): Quote {
            $quote = Quote::create([
                'status' => $attributes['status'] ?? QuoteStatus::Draft,
                'customer_name' => $attributes['customer_name'],
                'customer_email' => $attributes['customer_email'],
                'customer_phone' => $attributes['customer_phone'] ?? null,
                'licence_status' => $attributes['licence_status'] ?? null,
                'platform' => $result->selection->platform,
                'chambering' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->chambering
                    : null,
                'barrel_length' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->barrelLength
                    : null,
                'barrel_twist' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->barrelTwist
                    : null,
                'barrel_finish' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->barrelFinish
                    : null,
                'subtotal_cents' => $result->subtotalCents,
                'discount_amount_cents' => $result->discountCents,
                'total_cents' => $result->totalCents,
                'vat_amount_cents' => $result->vatCents,
                'deposit_percent' => $result->selection->depositPercent,
                'lead_time' => $attributes['lead_time'] ?? $result->leadTime,
                'notes' => $attributes['notes'] ?? null,
                'valid_until' => $attributes['valid_until'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);

            $this->replaceLines($quote, $result->lines);

            return $quote->load('lines');
        });
    }

    public function syncQuote(Quote $quote, BuildSelection $selection): Quote
    {
        $result = $this->evaluate($selection);

        return DB::transaction(function () use ($quote, $result): Quote {
            $quote->update([
                'platform' => $result->selection->platform,
                'chambering' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->chambering
                    : null,
                'barrel_length' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->barrelLength
                    : null,
                'barrel_twist' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->barrelTwist
                    : null,
                'barrel_finish' => $result->selection->platform === RiflePlatform::Separate
                    ? $result->selection->barrelFinish
                    : null,
                'subtotal_cents' => $result->subtotalCents,
                'discount_amount_cents' => $result->discountCents,
                'total_cents' => $result->totalCents,
                'vat_amount_cents' => $result->vatCents,
                'deposit_percent' => $result->selection->depositPercent,
                'lead_time' => $quote->lead_time ?: $result->leadTime,
            ]);

            $this->replaceLines($quote, $result->lines);

            return $quote->fresh('lines');
        });
    }

    public function selectionFromQuote(Quote $quote): BuildSelection
    {
        $quote->loadMissing('lines.component.category');

        $singles = [];
        $multis = [];
        $quantities = [];

        foreach ($quote->lines as $line) {
            $component = $line->component;
            if ($component === null || $component->is_automatic) {
                continue;
            }

            $key = $component->category?->key;
            if ($key === null) {
                continue;
            }

            if ($component->category?->selection_mode->value === 'multi') {
                $multis[$key] ??= [];
                $multis[$key][] = (int) $component->id;
                if ((int) $line->quantity > 1) {
                    $quantities[(int) $component->id] = (int) $line->quantity;
                }
            } else {
                $singles[$key] = (int) $component->id;
            }
        }

        return new BuildSelection(
            platform: $quote->platform,
            singles: $singles,
            multis: $multis,
            quantities: $quantities,
            chambering: $quote->chambering ?? '6.5 Creedmoor',
            barrelLength: $quote->barrel_length ?? '26"',
            barrelTwist: $quote->barrel_twist ?? '1:8',
            barrelFinish: $quote->barrel_finish ?? 'Bead-blast stainless',
            discountPercent: 0,
            discountAmountCents: (int) $quote->discount_amount_cents,
            depositPercent: (int) $quote->deposit_percent,
        );
    }

    /**
     * @return Collection<int, Component>
     */
    public function loadAvailable(): Collection
    {
        return Component::query()
            ->with('category')
            ->available()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, ComponentCategory>
     */
    public function visibleCategories(): Collection
    {
        return ComponentCategory::query()->visible()->get();
    }

    /**
     * @param  Collection<int, Component>  $components
     */
    public function clearIncompatible(BuildSelection $selection, ?Collection $components = null): BuildSelection
    {
        $components ??= $this->loadAvailable()->keyBy('id');
        $singles = $selection->singles;
        $footprint = $this->footprint($selection, $components);
        $tube = $this->opticTube($selection, $components);

        $chassisId = $singles['chassis'] ?? null;
        if ($chassisId && $footprint) {
            $chassis = $components->get((int) $chassisId);
            if ($chassis && $this->footprintReason($chassis, $footprint) !== null) {
                unset($singles['chassis']);
            }
        }

        $mountId = $singles['mount'] ?? null;
        if ($mountId && $tube) {
            $mount = $components->get((int) $mountId);
            if ($mount && $this->tubeReason($mount, $tube) !== null) {
                unset($singles['mount']);
            }
        }

        $selection = $selection->withSingles($singles);

        if ($this->requiresAftermarketTrigger($selection, $components)) {
            $triggerId = $singles['trigger'] ?? null;
            if ($triggerId) {
                $trigger = $components->get((int) $triggerId);
                if ($trigger && (bool) $trigger->is_factory_option) {
                    unset($singles['trigger']);
                    $selection = $selection->withSingles($singles);
                }
            }
        }

        return $selection;
    }

    /**
     * Some actions and barrelled actions ship without a usable trigger.
     * When flagged, the factory-keep option is not allowed and the trigger
     * step must resolve to an aftermarket component.
     *
     * @param  Collection<int, Component>  $components
     */
    public function requiresAftermarketTrigger(BuildSelection $selection, ?Collection $components = null): bool
    {
        $components ??= $this->loadAvailable()->keyBy('id');

        $id = $selection->platform === RiflePlatform::Barrelled
            ? $selection->selectedId('barrelled')
            : $selection->selectedId('action');

        if ($id === null) {
            return false;
        }

        return (bool) ($components->get($id)?->requires_aftermarket_trigger ?? false);
    }

    /**
     * @param  Collection<int, Component>  $components
     */
    protected function hasAftermarketTrigger(BuildSelection $selection, Collection $components): bool
    {
        $id = $selection->selectedId('trigger');
        if ($id === null) {
            return false;
        }

        $trigger = $components->get($id);

        return $trigger !== null && ! (bool) $trigger->is_factory_option;
    }

    /**
     * @param  Collection<int, Component>  $components
     * @return list<BuildLine>
     */
    protected function buildLines(BuildSelection $selection, Collection $components): array
    {
        $lines = [];
        $categories = ComponentCategory::query()->visible()->get()->keyBy('key');

        foreach ($categories as $key => $category) {
            if ($this->categoryHiddenForPlatform($key, $selection->platform)) {
                continue;
            }

            $ids = $category->selection_mode->value === 'multi'
                ? $selection->selectedIds($key)
                : array_filter([$selection->selectedId($key)]);

            foreach ($ids as $id) {
                $component = $components->get($id);
                if ($component === null) {
                    continue;
                }

                $qty = $component->allows_quantity ? $selection->quantity($id) : 1;
                $unit = (int) $component->price_cents;
                $unitCost = (int) $component->cost_cents;
                $specs = is_array($component->specs) ? array_values($component->specs) : [];

                if ($key === 'barrel') {
                    $specs = array_values(array_unique([
                        ...$specs,
                        implode(' · ', array_filter([
                            $selection->chambering,
                            $selection->barrelLength,
                            $selection->barrelTwist,
                        ])),
                    ]));
                }

                $lines[] = new BuildLine(
                    group: $category->name,
                    componentId: (int) $component->id,
                    brand: $component->brand,
                    description: $component->name,
                    specs: $specs,
                    quantity: $qty,
                    unitPriceCents: $unit,
                    lineTotalCents: $unit * $qty,
                    unitCostCents: $unitCost,
                    lineCostCents: $unitCost * $qty,
                );
            }
        }

        return $lines;
    }

    /**
     * @param  Collection<int, Component>  $components
     * @param  list<BuildLine>  $lines
     * @return list<BuildLine>
     */
    protected function appendLabour(BuildSelection $selection, Collection $components, array $lines): array
    {
        $slugs = config('tuneup.rifle_builder.labour_slugs', []);
        $labour = Component::query()
            ->whereIn('slug', array_values($slugs))
            ->where('is_automatic', true)
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        $hasBarrel = $selection->selectedId('barrel') !== null;
        $hasCore = $selection->selectedId('barrelled') !== null
            || $selection->selectedId('action') !== null
            || $hasBarrel;

        if ($selection->platform === RiflePlatform::Separate && $hasBarrel) {
            $chamber = $labour->get($slugs['chambering'] ?? '');
            if ($chamber) {
                $specs = array_values(array_filter([
                    implode(' · ', array_filter([
                        $selection->chambering,
                        $selection->barrelLength,
                        $selection->barrelTwist,
                    ])),
                ]));
                $lines[] = $this->labourLine($chamber, $specs);
            }
        }

        if ($hasCore) {
            $assembly = $labour->get($slugs['assembly'] ?? '');
            if ($assembly) {
                $lines[] = $this->labourLine($assembly, [
                    'Torqued to spec, headspace & function verified',
                ]);
            }
        }

        return $lines;
    }

    /**
     * @param  list<string>  $specs
     */
    protected function labourLine(Component $component, array $specs): BuildLine
    {
        $unit = (int) $component->price_cents;
        $unitCost = (int) $component->cost_cents;

        return new BuildLine(
            group: 'Gunsmithing',
            componentId: (int) $component->id,
            brand: $component->brand,
            description: $component->name,
            specs: $specs !== [] ? $specs : (is_array($component->specs) ? $component->specs : []),
            quantity: 1,
            unitPriceCents: $unit,
            lineTotalCents: $unit,
            unitCostCents: $unitCost,
            lineCostCents: $unitCost,
            isAutomatic: true,
        );
    }

    protected function discountCents(int $subtotal, BuildSelection $selection): int
    {
        $percent = max(0, $selection->discountPercent);
        $flat = max(0, $selection->discountAmountCents);
        $discount = (int) round($subtotal * $percent / 100) + $flat;

        return min($discount, $subtotal);
    }

    /**
     * @param  Collection<int, Component>  $components
     */
    protected function leadTime(BuildSelection $selection, Collection $components): string
    {
        $weeks = [];
        $ids = array_values($selection->singles);
        foreach ($selection->multis as $list) {
            $ids = array_merge($ids, $list);
        }

        foreach ($ids as $id) {
            $component = $components->get((int) $id);
            if ($component?->lead_time_weeks) {
                $weeks[] = (int) $component->lead_time_weeks;
            }
        }

        if ($weeks === []) {
            return (string) config('tuneup.rifle_builder.default_lead_time', '8–12 weeks');
        }

        $max = max($weeks);
        $buffer = (int) config('tuneup.rifle_builder.lead_time_buffer_weeks', 4);

        return $max.'–'.($max + $buffer).' weeks';
    }

    /**
     * @param  Collection<int, Component>  $components
     */
    protected function footprint(BuildSelection $selection, Collection $components): ?string
    {
        $id = $selection->platform === RiflePlatform::Barrelled
            ? $selection->selectedId('barrelled')
            : $selection->selectedId('action');

        if ($id === null) {
            return null;
        }

        return $components->get($id)?->footprint;
    }

    /**
     * @param  Collection<int, Component>  $components
     */
    protected function opticTube(BuildSelection $selection, Collection $components): ?string
    {
        $id = $selection->selectedId('optic');

        return $id ? $components->get($id)?->tube_diameter : null;
    }

    /**
     * @param  Collection<int, Component>  $components
     * @return array<int, string>
     */
    protected function disabledReasons(Collection $components, ?string $footprint, ?string $tube, bool $triggerRequired = false): array
    {
        $reasons = [];

        foreach ($components as $component) {
            $key = $component->category?->key;
            $reason = match ($key) {
                'chassis' => $footprint ? $this->footprintReason($component, $footprint) : null,
                'mount' => $tube ? $this->tubeReason($component, $tube) : null,
                'trigger' => $triggerRequired && (bool) $component->is_factory_option
                    ? 'This action requires an aftermarket trigger'
                    : null,
                default => null,
            };

            if ($reason !== null) {
                $reasons[(int) $component->id] = $reason;
            }
        }

        return $reasons;
    }

    public function footprintReason(Component $component, string $footprint): ?string
    {
        $fits = $component->fits_footprints ?? [];
        if ($fits === [] || in_array($footprint, $fits, true)) {
            return null;
        }

        $labels = config('tuneup.rifle_builder.footprint_labels', []);
        $label = $labels[$footprint] ?? $footprint;

        return 'Does not fit '.$label.' footprint';
    }

    public function tubeReason(Component $component, string $tube): ?string
    {
        $fits = $component->fits_tube_diameters ?? [];
        if ($fits === [] || in_array($tube, $fits, true)) {
            return null;
        }

        return 'Tube size mismatch — optic is '.$tube.' mm';
    }

    protected function categoryHiddenForPlatform(string $key, RiflePlatform $platform): bool
    {
        return match ($key) {
            'barrelled' => $platform !== RiflePlatform::Barrelled,
            'action', 'barrel' => $platform !== RiflePlatform::Separate,
            default => false,
        };
    }

    /**
     * @param  list<BuildLine>  $lines
     */
    protected function replaceLines(Quote $quote, array $lines): void
    {
        $quote->lines()->delete();

        foreach ($lines as $index => $line) {
            $quote->lines()->create([
                'component_id' => $line->componentId,
                'group_label' => $line->group,
                'brand' => $line->brand,
                'description' => $line->description,
                'specs' => $line->specs,
                'quantity' => $line->quantity,
                'unit_price_cents' => $line->unitPriceCents,
                'line_total_cents' => $line->lineTotalCents,
                'unit_cost_cents' => $line->unitCostCents,
                'sort_order' => $index + 1,
            ]);
        }
    }
}

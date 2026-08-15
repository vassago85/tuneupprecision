<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\RiflePlatform;
use App\Models\Component as CatalogueComponent;
use App\Models\ComponentCategory;
use App\Models\Quote;
use App\RifleBuilder\BuildResult;
use App\RifleBuilder\BuildSelection;
use App\Services\RifleBuildService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RifleBuildPicker extends Component
{
    public bool $dealerMode = false;

    public ?int $quoteId = null;

    /** @var array<string, mixed> */
    public array $initialSelection = [];

    public string $platform = 'barrelled';

    /** @var array<string, int|null> */
    public array $singles = [];

    /** @var array<string, list<int>> */
    public array $multis = [];

    /** @var array<int, int> */
    public array $quantities = [];

    public string $chambering = '6.5 Creedmoor';

    public string $barrelLength = '26"';

    public string $barrelTwist = '1:8';

    public string $barrelFinish = 'Bead-blast stainless';

    public int $discountPercent = 0;

    public int $discountAmountCents = 0;

    public int $depositPercent = 50;

    /** @var list<string> */
    public array $openSteps = ['platform'];

    public function mount(): void
    {
        if ($this->quoteId) {
            $quote = Quote::query()->with('lines.component.category')->find($this->quoteId);
            if ($quote) {
                $this->applySelection(app(RifleBuildService::class)->selectionFromQuote($quote));
            }
        } elseif ($this->initialSelection !== []) {
            $this->applySelection(BuildSelection::fromArray($this->initialSelection));
        }

        if ($this->openSteps === []) {
            $this->openSteps = ['platform'];
        }
    }

    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
        $this->singles['barrelled'] = null;
        $this->singles['action'] = null;
        $this->singles['barrel'] = null;
        $this->singles['chassis'] = null;
        $this->advanceFrom('platform');
        $this->emitChange();
    }

    public function selectSingle(string $key, int $id): void
    {
        $current = $this->singles[$key] ?? null;
        $this->singles[$key] = $current === $id ? null : $id;

        if ($this->singles[$key]) {
            $this->advanceFrom($key);
        }

        $this->emitChange();
    }

    public function toggleMulti(string $key, int $id): void
    {
        $ids = $this->multis[$key] ?? [];
        if (in_array($id, $ids, true)) {
            $this->multis[$key] = array_values(array_filter($ids, fn (int $existing): bool => $existing !== $id));
        } else {
            $this->multis[$key] = [...$ids, $id];
            $this->quantities[$id] ??= 1;
        }

        $this->emitChange();
    }

    public function setQuantity(int $id, int $qty): void
    {
        $this->quantities[$id] = max(1, $qty);
        $this->emitChange();
    }

    public function toggleStep(string $id): void
    {
        if (in_array($id, $this->openSteps, true)) {
            $this->openSteps = array_values(array_filter($this->openSteps, fn (string $step): bool => $step !== $id));
        } else {
            $this->openSteps[] = $id;
        }
    }

    public function selection(): BuildSelection
    {
        return new BuildSelection(
            platform: RiflePlatform::from($this->platform),
            singles: array_filter($this->singles, fn ($id) => filled($id)),
            multis: $this->multis,
            quantities: $this->quantities,
            chambering: $this->chambering,
            barrelLength: $this->barrelLength,
            barrelTwist: $this->barrelTwist,
            barrelFinish: $this->barrelFinish,
            discountPercent: $this->discountPercent,
            discountAmountCents: $this->discountAmountCents,
            depositPercent: $this->depositPercent,
        );
    }

    #[Computed]
    public function result(): BuildResult
    {
        return app(RifleBuildService::class)->evaluate($this->selection());
    }

    /**
     * @return list<array<string, mixed>>
     */
    #[Computed]
    public function steps(): array
    {
        $categories = ComponentCategory::query()->visible()->get()->keyBy('key');
        $platform = RiflePlatform::from($this->platform);
        $out = [
            ['id' => 'platform', 'title' => 'Platform', 'type' => 'platform', 'optional' => false, 'hint' => null, 'config' => null],
        ];

        foreach ($categories as $key => $category) {
            if ($key === 'barrelled' && $platform !== RiflePlatform::Barrelled) {
                continue;
            }
            if (in_array($key, ['action', 'barrel'], true) && $platform !== RiflePlatform::Separate) {
                continue;
            }

            $out[] = [
                'id' => $key,
                'title' => $category->name,
                'type' => $category->selection_mode->value,
                'optional' => $category->is_optional,
                'hint' => $category->hint,
                'config' => $key === 'barrel' ? 'barrel' : null,
                'allows_quantity' => $category->allows_quantity,
            ];
        }

        return $out;
    }

    /**
     * @return Collection<int, CatalogueComponent>
     */
    #[Computed]
    public function catalogue(): Collection
    {
        return app(RifleBuildService::class)->loadAvailable()->groupBy(fn (CatalogueComponent $c) => $c->category?->key);
    }

    public function render()
    {
        return view('livewire.rifle-build-picker');
    }

    protected function applySelection(BuildSelection $selection): void
    {
        $this->platform = $selection->platform->value;
        $this->singles = $selection->singles;
        $this->multis = $selection->multis;
        $this->quantities = $selection->quantities;
        $this->chambering = $selection->chambering ?? '6.5 Creedmoor';
        $this->barrelLength = $selection->barrelLength ?? '26"';
        $this->barrelTwist = $selection->barrelTwist ?? '1:8';
        $this->barrelFinish = $selection->barrelFinish ?? 'Bead-blast stainless';
        $this->discountPercent = $selection->discountPercent;
        $this->discountAmountCents = $selection->discountAmountCents;
        $this->depositPercent = $selection->depositPercent;
    }

    protected function advanceFrom(string $stepId): void
    {
        $this->openSteps = array_values(array_filter($this->openSteps, fn (string $id): bool => $id !== $stepId));
        $list = $this->steps;
        $index = collect($list)->search(fn (array $step): bool => $step['id'] === $stepId);
        if ($index === false) {
            return;
        }

        $next = collect($list)->slice($index + 1)->first(function (array $step): bool {
            if ($step['type'] === 'multi') {
                return false;
            }
            if ($step['type'] === 'platform') {
                return false;
            }

            return empty($this->singles[$step['id']] ?? null);
        });

        if ($next) {
            $this->openSteps[] = $next['id'];
        } elseif (isset($list[$index + 1])) {
            $this->openSteps[] = $list[$index + 1]['id'];
        }
    }

    protected function emitChange(): void
    {
        $result = app(RifleBuildService::class)->evaluate($this->selection());
        $this->singles = $result->selection->singles;

        $this->dispatch('rifle-build-changed', selection: $result->selection->toArray());
    }

    public function updatedChambering(): void
    {
        $this->emitChange();
    }

    public function updatedBarrelLength(): void
    {
        $this->emitChange();
    }

    public function updatedBarrelTwist(): void
    {
        $this->emitChange();
    }

    public function updatedBarrelFinish(): void
    {
        $this->emitChange();
    }

    public function updatedDiscountPercent(): void
    {
        $this->emitChange();
    }

    public function updatedDiscountAmountCents(): void
    {
        $this->emitChange();
    }

    public function updatedDepositPercent(): void
    {
        $this->emitChange();
    }
}

<?php

declare(strict_types=1);

namespace App\RifleBuilder;

use App\Enums\RiflePlatform;

final readonly class BuildSelection
{
    /**
     * @param  array<string, int>  $singles  category key => component id
     * @param  array<string, list<int>>  $multis  category key => component ids
     * @param  array<int, int>  $quantities  component id => qty
     */
    public function __construct(
        public RiflePlatform $platform = RiflePlatform::Barrelled,
        public array $singles = [],
        public array $multis = [],
        public array $quantities = [],
        public ?string $chambering = '6.5 Creedmoor',
        public ?string $barrelLength = '26"',
        public ?string $barrelTwist = '1:8',
        public ?string $barrelFinish = 'Bead-blast stainless',
        public int $discountPercent = 0,
        public int $discountAmountCents = 0,
        public int $depositPercent = 50,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $platform = $data['platform'] ?? RiflePlatform::Barrelled->value;

        return new self(
            platform: $platform instanceof RiflePlatform
                ? $platform
                : RiflePlatform::from((string) $platform),
            singles: array_map('intval', $data['singles'] ?? []),
            multis: array_map(
                fn (mixed $ids): array => array_values(array_map('intval', (array) $ids)),
                $data['multis'] ?? [],
            ),
            quantities: array_map('intval', $data['quantities'] ?? []),
            chambering: $data['chambering'] ?? '6.5 Creedmoor',
            barrelLength: $data['barrel_length'] ?? '26"',
            barrelTwist: $data['barrel_twist'] ?? '1:8',
            barrelFinish: $data['barrel_finish'] ?? 'Bead-blast stainless',
            discountPercent: (int) ($data['discount_percent'] ?? 0),
            discountAmountCents: (int) ($data['discount_amount_cents'] ?? 0),
            depositPercent: (int) ($data['deposit_percent'] ?? config('tuneup.rifle_builder.default_deposit_percent', 50)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'platform' => $this->platform->value,
            'singles' => $this->singles,
            'multis' => $this->multis,
            'quantities' => $this->quantities,
            'chambering' => $this->chambering,
            'barrel_length' => $this->barrelLength,
            'barrel_twist' => $this->barrelTwist,
            'barrel_finish' => $this->barrelFinish,
            'discount_percent' => $this->discountPercent,
            'discount_amount_cents' => $this->discountAmountCents,
            'deposit_percent' => $this->depositPercent,
        ];
    }

    public function withSingles(array $singles): self
    {
        return new self(
            platform: $this->platform,
            singles: $singles,
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

    public function selectedId(string $categoryKey): ?int
    {
        $id = $this->singles[$categoryKey] ?? null;

        return $id ? (int) $id : null;
    }

    /**
     * @return list<int>
     */
    public function selectedIds(string $categoryKey): array
    {
        return array_values(array_map('intval', $this->multis[$categoryKey] ?? []));
    }

    public function quantity(int $componentId): int
    {
        return max(1, (int) ($this->quantities[$componentId] ?? 1));
    }
}

<?php

declare(strict_types=1);

namespace App\RifleBuilder;

final readonly class BuildResult
{
    /**
     * @param  list<BuildLine>  $lines
     * @param  array<int, string|null>  $disabledReasons  component id => reason (null = available)
     */
    public function __construct(
        public BuildSelection $selection,
        public array $lines,
        public int $subtotalCents,
        public int $discountCents,
        public int $totalCents,
        public int $exVatCents,
        public int $vatCents,
        public int $costCents,
        public int $grossProfitCents,
        public float $marginPercent,
        public int $depositCents,
        public string $leadTime,
        public ?string $footprint,
        public ?string $opticTube,
        public array $disabledReasons,
        public bool $requiresAftermarketTrigger = false,
        public bool $needsTriggerChoice = false,
    ) {}

    public function componentCount(): int
    {
        return count($this->lines);
    }
}

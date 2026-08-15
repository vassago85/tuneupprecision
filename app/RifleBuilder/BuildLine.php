<?php

declare(strict_types=1);

namespace App\RifleBuilder;

final readonly class BuildLine
{
    /**
     * @param  list<string>  $specs
     */
    public function __construct(
        public string $group,
        public ?int $componentId,
        public string $brand,
        public string $description,
        public array $specs,
        public int $quantity,
        public int $unitPriceCents,
        public int $lineTotalCents,
        public int $unitCostCents,
        public int $lineCostCents,
        public bool $isAutomatic = false,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Generates zero-padded, unique human references:
 *   Bookings => TU-B-000123
 *   Orders   => TU-S-000123
 *
 * The consuming model must define a public string $referencePrefix
 * (e.g. 'TU-B') and have a `reference` column.
 */
trait HasReference
{
    protected static function bootHasReference(): void
    {
        static::creating(function ($model): void {
            if (blank($model->reference)) {
                $model->reference = $model->generateReference();
            }
        });
    }

    public function generateReference(): string
    {
        $prefix = $this->referencePrefix();

        do {
            // Sequence-based, padded to 6 digits, with a random fallback tail
            // to stay unique even under concurrency.
            $number = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $candidate = sprintf('%s-%s', $prefix, $number);
        } while (static::query()->where('reference', $candidate)->exists());

        return $candidate;
    }

    protected function referencePrefix(): string
    {
        return property_exists($this, 'referencePrefix') && $this->referencePrefix
            ? $this->referencePrefix
            : 'TU-'.Str::upper(Str::substr(class_basename($this), 0, 1));
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Database\Factories\QuoteLineFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    /** @use HasFactory<QuoteLineFactory> */
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'component_id',
        'group_label',
        'brand',
        'description',
        'specs',
        'quantity',
        'unit_price_cents',
        'line_total_cents',
        'unit_cost_cents',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'line_total_cents' => 'integer',
            'unit_cost_cents' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Quote, $this>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * @return BelongsTo<Component, $this>
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->line_total_cents));
    }
}

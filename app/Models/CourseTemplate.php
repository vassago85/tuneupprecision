<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Database\Factories\CourseTemplateFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CourseTemplate extends Model implements HasMedia
{
    /** @use HasFactory<CourseTemplateFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /** Max images allowed in the course gallery. */
    public const int MAX_GALLERY_IMAGES = 5;

    protected $fillable = [
        'training_type_id',
        'title',
        'slug',
        'level',
        'blurb',
        'specs',
        'base_price_cents',
        'default_capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'base_price_cents' => 'integer',
            'default_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<TrainingType, $this>
     */
    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class);
    }

    /**
     * @return HasMany<TrainingEvent, $this>
     */
    public function trainingEvents(): HasMany
    {
        return $this->hasMany(TrainingEvent::class);
    }

    public function registerMediaCollections(): void
    {
        // The featured image used as the course thumbnail across the site.
        $this->addMediaCollection('thumbnail')->singleFile();

        // Up to MAX_GALLERY_IMAGES additional images (cap enforced in the form).
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->optimize()
            ->nonQueued();

        // Compressed, web-sized image for detail views (never serve the original).
        $this->addMediaConversion('web')
            ->fit(Fit::Max, 1600, 1600)
            ->optimize()
            ->nonQueued();
    }

    /**
     * The thumbnail URL: the featured image if set, else the first gallery
     * image, else null. Pass a conversion (e.g. 'thumb') or omit for original.
     */
    public function thumbnailUrl(?string $conversion = 'thumb'): ?string
    {
        $media = $this->getFirstMedia('thumbnail') ?? $this->getFirstMedia('images');

        if ($media === null) {
            return null;
        }

        return $conversion !== null && $media->hasGeneratedConversion($conversion)
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }

    /**
     * Display price, e.g. "R1 850.00".
     */
    protected function basePrice(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->base_price_cents));
    }
}

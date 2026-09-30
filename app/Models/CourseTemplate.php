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
     * Absolute image URL for link previews (WhatsApp, etc.). The course's
     * own tile — featured thumbnail, else the first gallery image.
     */
    public function shareImageUrl(): ?string
    {
        $url = $this->thumbnailUrl('web') ?? $this->thumbnailUrl(null);

        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    /**
     * Squad size for the public spec sheet.
     *
     * Listed dates that share one max-participants value win, so "N shooters"
     * matches "x of N seats left". With no dates, the course's own max is
     * used. Dates with different maxima return null so the card does not
     * print a squad size that disagrees with one of them.
     *
     * @param  iterable<mixed>  $capacities
     */
    public static function squadCapacity(iterable $capacities, ?int $fallback): ?int
    {
        $unique = collect($capacities)
            ->map(fn ($value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values();

        if ($unique->count() === 1) {
            return (int) $unique->first();
        }

        if ($unique->isEmpty() && $fallback !== null && $fallback > 0) {
            return $fallback;
        }

        return null;
    }

    /**
     * Public spec rows. Any stored Squad value is dropped; the squad size
     * always comes from max participants.
     *
     * @param  array<array-key, mixed>  $specs
     * @return array<array-key, mixed>
     */
    public static function specsWithSquad(array $specs, ?int $maxParticipants): array
    {
        $rows = [];

        foreach ($specs as $label => $value) {
            if (strcasecmp((string) $label, 'Squad') === 0) {
                continue;
            }

            $rows[$label] = $value;
        }

        if ($maxParticipants !== null && $maxParticipants > 0) {
            $rows['Squad'] = $maxParticipants === 1
                ? '1 shooter'
                : $maxParticipants.' shooters';
        }

        return $rows;
    }

    /**
     * Display price, e.g. "R1 850.00".
     */
    protected function basePrice(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->base_price_cents));
    }
}

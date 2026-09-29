<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\YoutubeId;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Video extends Model implements HasMedia
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'slug',
        'title',
        'caption',
        'training_type_id',
        'youtube_id',
        'is_featured',
        'is_members_only',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'training_type_id' => 'integer',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_members_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        // Optional custom poster. If none is uploaded we fall back to the
        // YouTube thumbnail (see thumbnailUrl()).
        $this->addMediaCollection('poster')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        // Optional uploaded video. When present, the facade plays this
        // instead of the YouTube iframe. Public disk so the URL is reachable
        // from the site (the default disk can be private).
        $this->addMediaCollection('file')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/quicktime']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('poster')
            ->fit(Fit::Cover, 400, 225)
            ->optimize()
            ->nonQueued();

        $this->addMediaConversion('wide')
            ->performOnCollections('poster')
            ->fit(Fit::Max, 1280, 720)
            ->optimize()
            ->nonQueued();
    }

    /**
     * @return BelongsTo<TrainingType, $this>
     */
    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class);
    }

    /**
     * @param  Builder<Video>  $query
     */
    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    /**
     * Thumbnail for the facade: uploaded poster (thumb conversion) → wide
     * conversion → YouTube's `hqdefault` → null. Callers should treat a null
     * as "no thumbnail available".
     */
    public function thumbnailUrl(string $conversion = 'thumb'): ?string
    {
        $media = $this->getFirstMedia('poster');
        if ($media !== null) {
            $url = $media->getUrl($conversion);
            if ($url !== '') {
                return $url;
            }
        }

        $youtubeId = $this->youtubeEmbedId();
        if ($youtubeId !== null) {
            return "https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg";
        }

        return null;
    }

    /**
     * True when the admin uploaded an MP4 (native player wins over YouTube).
     */
    public function hasNativeVideo(): bool
    {
        return $this->getFirstMedia('file') !== null;
    }

    /**
     * Playable URL for the facade: uploaded MP4 → nocookie YouTube embed URL
     * → null. The blade decides between `<video>` and `<iframe>` based on
     * hasNativeVideo().
     */
    public function videoUrl(): ?string
    {
        if ($this->hasNativeVideo()) {
            return $this->getFirstMediaUrl('file');
        }

        $youtubeId = $this->youtubeEmbedId();
        if ($youtubeId !== null) {
            return "https://www.youtube-nocookie.com/embed/{$youtubeId}?autoplay=1&rel=0";
        }

        return null;
    }

    /**
     * Store a bare ID even when the admin pastes a full YouTube URL.
     */
    protected function youtubeId(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value): ?string => YoutubeId::extract($value),
        );
    }

    private function youtubeEmbedId(): ?string
    {
        return YoutubeId::extract($this->youtube_id);
    }
}

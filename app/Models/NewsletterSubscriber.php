<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriberStatus;
use Database\Factories\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'status',
        'token',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriberStatus::class,
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $subscriber): void {
            $subscriber->token ??= Str::random(48);
            $subscriber->status ??= SubscriberStatus::Subscribed;
            $subscriber->subscribed_at ??= now();
        });
    }

    /**
     * @param  Builder<NewsletterSubscriber>  $query
     */
    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->where('status', SubscriberStatus::Subscribed);
    }

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', ['token' => $this->token]);
    }
}

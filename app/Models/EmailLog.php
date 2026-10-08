<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Str;

/**
 * One row per outgoing email: handed to the mail provider (sent) or given up
 * on after the queue's last retry (failed). "Sent" means the provider
 * accepted it — inbox delivery is confirmed in the Mailgun logs, matched on
 * message_id.
 */
class EmailLog extends Model
{
    use Prunable;

    public const string SENT = 'sent';

    public const string FAILED = 'failed';

    /**
     * Logs hold customer names and addresses, so they are not kept forever.
     */
    public const int KEEP_DAYS = 180;

    protected $fillable = [
        'status',
        'mailable',
        'mailer',
        'recipients',
        'subject',
        'message_id',
        'error',
    ];

    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<', now()->subDays(self::KEEP_DAYS));
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::FAILED);
    }

    /**
     * "BookingPlaced" -> "Booking placed"; raw/test messages have no class.
     */
    public function typeLabel(): string
    {
        return $this->mailable
            ? Str::of(class_basename($this->mailable))->snake(' ')->ucfirst()->toString()
            : 'Plain message';
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Throwable;

/**
 * Writes the admin email log from Laravel's mail and queue events.
 */
final class EmailLogger
{
    public static function sent(MessageSent $event): void
    {
        $message = $event->message;

        self::record([
            'status' => EmailLog::SENT,
            'mailable' => $event->data['__laravel_mailable'] ?? null,
            'mailer' => $event->data['mailer'] ?? config('mail.default'),
            'recipients' => self::addresses([...$message->getTo(), ...$message->getCc(), ...$message->getBcc()]),
            'subject' => $message->getSubject(),
            'message_id' => $event->sent->getMessageId(),
        ]);
    }

    /**
     * A queued email that ran out of retries. Earlier attempts that later
     * succeed are not logged as failures.
     */
    public static function failed(JobFailed $event): void
    {
        $payload = $event->job->payload();

        if (($payload['data']['commandName'] ?? null) !== SendQueuedMailable::class) {
            return;
        }

        $mailable = self::mailableFrom($payload['data']['command'] ?? '');

        self::record([
            'status' => EmailLog::FAILED,
            'mailable' => $mailable ? $mailable::class : ($payload['displayName'] ?? null),
            'mailer' => $mailable?->mailer ?? config('mail.default'),
            'recipients' => $mailable ? self::addresses(array_column($mailable->to, 'address')) : '',
            'subject' => $mailable ? self::subjectOf($mailable) : null,
            'error' => $event->exception->getMessage(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function record(array $attributes): void
    {
        if (isset($attributes['error'])) {
            $attributes['error'] = Str::limit((string) $attributes['error'], 2000);
        }

        if (isset($attributes['subject'])) {
            $attributes['subject'] = Str::limit((string) $attributes['subject'], 250);
        }

        // Logging must never stop an email from going out.
        try {
            EmailLog::query()->create($attributes);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @param  array<int, Address|string>  $addresses
     */
    private static function addresses(array $addresses): string
    {
        return collect($addresses)
            ->map(fn (Address|string $address): string => $address instanceof Address ? $address->getAddress() : $address)
            ->filter()
            ->unique()
            ->implode(', ');
    }

    private static function mailableFrom(string $serialized): ?Mailable
    {
        try {
            $command = unserialize($serialized);
        } catch (Throwable) {
            // Booking/order deleted since queueing, or an encrypted payload.
            return null;
        }

        return $command instanceof SendQueuedMailable && $command->mailable instanceof Mailable
            ? $command->mailable
            : null;
    }

    private static function subjectOf(Mailable $mailable): ?string
    {
        if ($mailable->subject) {
            return $mailable->subject;
        }

        try {
            return method_exists($mailable, 'envelope') ? $mailable->envelope()->subject : null;
        } catch (Throwable) {
            return null;
        }
    }
}

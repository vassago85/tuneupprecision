<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingEvents\Actions;

use App\Enums\BookingStatus;
use App\Mail\TestimonialInvitation;
use App\Models\Booking;
use App\Models\TrainingEvent;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Emails every confirmed shooter on the event a signed URL that opens the
 * public testimonial submit form pre-filled with their name and the event.
 * By default skips shooters already invited so the resend is opt-in.
 * Only training events (not competitions) — competition RSVPs are guests.
 */
class SendTestimonialInvitesAction
{
    private const int INVITE_TTL_DAYS = 90;

    public static function make(): Action
    {
        return Action::make('sendTestimonialInvites')
            ->label('Send testimonial invites')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('primary')
            // Competition RSVPs are a different flow — hide the action on them.
            ->visible(fn (TrainingEvent $record): bool => ! $record->isCompetition())
            ->modalHeading('Email attendees to ask for a testimonial')
            ->modalSubmitActionLabel('Queue emails')
            ->fillForm(fn (): array => [
                'include_already_invited' => false,
                'allow_future_event' => false,
            ])
            ->schema([
                Placeholder::make('recipients_count')
                    ->label('Recipients')
                    ->content(fn (TrainingEvent $record, callable $get): string => self::describe(
                        $record,
                        (bool) $get('include_already_invited'),
                        (bool) $get('allow_future_event'),
                    )),
                Checkbox::make('include_already_invited')
                    ->label('Include shooters already invited')
                    ->helperText('By default, shooters already emailed once are skipped so they are not spammed.')
                    ->live(),
                Checkbox::make('allow_future_event')
                    ->label('Send even though this event has not happened yet')
                    ->helperText('Normally invites are sent after the event date. Tick to override.')
                    ->live()
                    ->visible(fn (TrainingEvent $record): bool => ! self::hasHappened($record)),
            ])
            ->action(function (array $data, TrainingEvent $record): void {
                $includeAlreadyInvited = (bool) ($data['include_already_invited'] ?? false);
                $allowFuture = (bool) ($data['allow_future_event'] ?? false);

                if (! self::hasHappened($record) && ! $allowFuture) {
                    Notification::make()
                        ->title('Event has not happened yet')
                        ->body('Tick "Send even though this event has not happened yet" to override.')
                        ->warning()
                        ->send();

                    return;
                }

                $queued = self::dispatch($record, $includeAlreadyInvited);

                if ($queued === 0) {
                    Notification::make()
                        ->title('No one to email')
                        ->body('There are no confirmed shooters with an email address to invite for this event.')
                        ->warning()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Testimonial invites queued')
                    ->body("Queued {$queued} email(s) to shooters on this event.")
                    ->success()
                    ->send();
            });
    }

    /**
     * Queue the invite mails for this event and stamp `testimonial_invited_at`
     * on each booking that gets a mail. Returns the number of mails queued.
     * Extracted so it can be tested without spinning up the Filament runtime.
     */
    public static function dispatch(TrainingEvent $event, bool $includeAlreadyInvited): int
    {
        $bookings = self::bookings($event, $includeAlreadyInvited);

        if ($bookings->isEmpty()) {
            return 0;
        }

        $queued = 0;
        foreach ($bookings as $booking) {
            Mail::to($booking->email)->queue(new TestimonialInvitation(
                booking: $booking,
                signedUrl: self::signedUrl($booking, $event),
            ));
            $booking->forceFill(['testimonial_invited_at' => now()])->save();
            $queued++;
        }

        return $queued;
    }

    private static function bookings(TrainingEvent $event, bool $includeAlreadyInvited)
    {
        return $event->bookings()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when(! $includeAlreadyInvited, fn ($q) => $q->whereNull('testimonial_invited_at'))
            ->get();
    }

    private static function describe(TrainingEvent $event, bool $includeAlreadyInvited, bool $allowFuture): string
    {
        if (! self::hasHappened($event) && ! $allowFuture) {
            return 'Event has not happened yet — tick the override to send anyway.';
        }

        $count = self::bookings($event, $includeAlreadyInvited)->count();

        if ($count === 0) {
            return $includeAlreadyInvited
                ? 'No confirmed shooters with an email address on this event.'
                : 'Everyone who could be invited has already been emailed once — tick the option to resend.';
        }

        return $count === 1 ? '1 shooter will be emailed' : "{$count} shooters will be emailed";
    }

    private static function hasHappened(TrainingEvent $event): bool
    {
        $end = $event->ends_on ?? $event->starts_on;

        return $end !== null && $end->isPast();
    }

    private static function signedUrl(Booking $booking, TrainingEvent $event): string
    {
        return URL::temporarySignedRoute(
            'testimonials.create',
            now()->addDays(self::INVITE_TTL_DAYS),
            [
                'name' => $booking->customer_name,
                'email' => $booking->email,
                'training_type_id' => $event->courseTemplate?->training_type_id
                    ?? $event->training_type_id,
                'training_event_id' => $event->id,
            ],
        );
    }
}

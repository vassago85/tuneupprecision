<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingEvents\Actions;

use App\Enums\BookingStatus;
use App\Mail\TrainingEventNotification;
use App\Models\Booking;
use App\Models\TrainingEvent;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Compose and send an email to just the people booked on one training event.
 * Reused as a table row action and on the event edit page header.
 */
class NotifyAttendeesAction
{
    public static function make(): Action
    {
        return Action::make('notifyAttendees')
            ->label('Notify attendees')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('primary')
            ->modalHeading('Email the people booked on this event')
            ->modalSubmitActionLabel('Send emails')
            ->fillForm(fn (TrainingEvent $record): array => [
                'audience' => 'active',
                'subject' => 'Update · '.(optional($record->courseTemplate)->title ?? 'your training'),
            ])
            ->schema([
                Placeholder::make('recipients_count')
                    ->label('Recipients')
                    ->content(fn (TrainingEvent $record, callable $get): string => self::describeAudience($record, $get('audience') ?? 'active')),
                Select::make('audience')
                    ->label('Send to')
                    ->options([
                        'active' => 'Everyone booked (confirmed + pending holds)',
                        'confirmed' => 'Confirmed bookings only',
                    ])
                    ->default('active')
                    ->required()
                    ->live(),
                TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(255),
                Textarea::make('message')
                    ->label('Message')
                    ->rows(8)
                    ->required()
                    ->helperText('Plain text. The event date and venue are added automatically at the bottom.'),
            ])
            ->action(function (array $data, TrainingEvent $record): void {
                $recipients = self::recipients($record, $data['audience'])
                    ->unique('email')
                    ->filter(fn ($booking): bool => filled($booking->email));

                if ($recipients->isEmpty()) {
                    Notification::make()
                        ->title('No one to email')
                        ->body('There are no matching bookings with an email address for this event.')
                        ->warning()
                        ->send();

                    return;
                }

                foreach ($recipients as $booking) {
                    Mail::to($booking->email)->queue(new TrainingEventNotification(
                        event: $record,
                        subjectLine: $data['subject'],
                        body: $data['message'],
                        recipientName: (string) $booking->customer_name,
                    ));
                }

                Notification::make()
                    ->title('Emails queued')
                    ->body("Queued {$recipients->count()} email(s) to attendees of this event.")
                    ->success()
                    ->send();
            });
    }

    /**
     * Booking statuses included for the chosen audience.
     *
     * @return array<int, BookingStatus>
     */
    private static function statusesFor(string $audience): array
    {
        return $audience === 'confirmed'
            ? [BookingStatus::Confirmed]
            : [BookingStatus::Confirmed, BookingStatus::Pending];
    }

    /**
     * @return Collection<int, Booking>
     */
    private static function recipients(TrainingEvent $event, string $audience)
    {
        return $event->bookings()
            ->whereIn('status', array_map(
                fn (BookingStatus $s): string => $s->value,
                self::statusesFor($audience),
            ))
            ->get(['id', 'customer_name', 'email']);
    }

    private static function describeAudience(TrainingEvent $event, string $audience): string
    {
        $count = self::recipients($event, $audience)
            ->filter(fn ($booking): bool => filled($booking->email))
            ->unique('email')
            ->count();

        return $count === 1 ? '1 person will be emailed' : "{$count} people will be emailed";
    }
}

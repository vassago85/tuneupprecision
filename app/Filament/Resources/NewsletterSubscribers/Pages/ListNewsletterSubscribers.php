<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Mail\Newsletter;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendNewsletter')
                ->label('Send newsletter')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('primary')
                ->modalHeading('Send the newsletter')
                ->modalSubmitActionLabel('Queue newsletter')
                ->schema([
                    Placeholder::make('recipients')
                        ->label('Recipients')
                        ->content(fn (): string => self::describeRecipients()),
                    TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('message')
                        ->label('Message')
                        ->rows(10)
                        ->required()
                        ->helperText('Plain text. An unsubscribe link is added automatically.'),
                ])
                ->action(function (array $data): void {
                    $subscribers = NewsletterSubscriber::query()->subscribed()->get();

                    if ($subscribers->isEmpty()) {
                        Notification::make()
                            ->title('No active subscribers')
                            ->body('There is no one subscribed to send to yet.')
                            ->warning()
                            ->send();

                        return;
                    }

                    foreach ($subscribers as $subscriber) {
                        Mail::to($subscriber->email)->queue(new Newsletter(
                            subscriber: $subscriber,
                            subjectLine: $data['subject'],
                            body: $data['message'],
                        ));
                    }

                    Notification::make()
                        ->title('Newsletter queued')
                        ->body("Queued {$subscribers->count()} email(s) to your active subscribers.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }

    private static function describeRecipients(): string
    {
        $count = NewsletterSubscriber::query()->subscribed()->count();

        return $count === 1 ? '1 active subscriber' : "{$count} active subscribers";
    }
}

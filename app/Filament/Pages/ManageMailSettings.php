<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\EmailLog;
use App\Support\EmailLogger;
use App\Support\MailSettings;
use App\Support\OwnerInbox;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Throwable;
use UnitEnum;

class ManageMailSettings extends Page
{
    protected string $view = 'filament.pages.manage-mail-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Email';

    protected static ?string $title = 'Email settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = MailSettings::details();

        // Never echo the stored secret back into the form — show a placeholder
        // instead and only overwrite it when a new value is typed.
        $settings['mailgun_secret'] = '';

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Delivery')
                    ->description('How the site sends transactional and notification emails. Changes apply immediately — no deploy needed.')
                    ->columns(2)
                    ->schema([
                        Select::make('mailer')
                            ->label('Mail driver')
                            ->options([
                                'log' => 'Log (write to laravel.log — testing only)',
                                'mailgun' => 'Mailgun (API)',
                                'smtp' => 'SMTP',
                            ])
                            ->required()
                            ->live()
                            ->helperText('Use Log while testing. Switch to Mailgun for live delivery.'),
                        TextInput::make('from_address')
                            ->label('From address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('from_name')
                            ->label('From name')
                            ->required()
                            ->maxLength(255),
                    ]),

                Section::make('Mailgun')
                    ->description('Credentials from your Mailgun dashboard (Sending → Domain settings → API keys).')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('mailer') === 'mailgun')
                    ->schema([
                        TextInput::make('mailgun_domain')
                            ->label('Domain')
                            ->placeholder('mg.tuneupprecision.co.za')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('mailer') === 'mailgun'),
                        TextInput::make('mailgun_endpoint')
                            ->label('API endpoint')
                            ->helperText('api.mailgun.net (US) or api.eu.mailgun.net (EU region).')
                            ->default('api.mailgun.net')
                            ->maxLength(255),
                        TextInput::make('mailgun_secret')
                            ->label('API key (secret)')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->placeholder('•••••••• — leave blank to keep the current key')
                            ->helperText('Stored encrypted. Leave blank to keep the existing key.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notifications')
                    ->description('Where the site sends owner alerts: new bookings, shop orders, build enquiries, contact-form enquiries, new testimonials and questionnaire answers.')
                    ->schema([
                        TextInput::make('notify_email')
                            ->label('Send all notifications to')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder(OwnerInbox::DEFAULT_EMAIL)
                            ->helperText('Customers still get their own confirmation emails — this only controls the copy that comes to you.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        MailSettings::save($this->form->getState());

        Notification::make()
            ->title('Email settings saved')
            ->body('New messages will use these settings straight away.')
            ->success()
            ->send();
    }

    public function sendTestAction(): Action
    {
        return Action::make('sendTest')
            ->label('Send test email')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('gray')
            ->modalDescription('Your changes on this page are saved first, then a test is sent using them.')
            ->modalSubmitActionLabel('Save & send')
            ->schema([
                TextInput::make('recipient')
                    ->label('Send to')
                    ->email()
                    ->required()
                    ->default(fn (): string => $this->data['notify_email'] ?? OwnerInbox::email()),
            ])
            ->action(function (array $data): void {
                MailSettings::save($this->form->getState());
                MailSettings::apply();
                Mail::forgetMailers();

                $subject = 'Tune Up Precision — test email';

                try {
                    Mail::raw(
                        "This is a test email from Tune Up Precision.\n\nIf you can read this, your mail settings are working.",
                        fn ($message) => $message
                            ->to($data['recipient'])
                            ->subject($subject),
                    );
                } catch (Throwable $e) {
                    EmailLogger::record([
                        'status' => EmailLog::FAILED,
                        'mailer' => config('mail.default'),
                        'recipients' => $data['recipient'],
                        'subject' => $subject,
                        'error' => $e->getMessage(),
                    ]);

                    Notification::make()
                        ->title('Test email failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Test email sent')
                    ->body("Settings saved and a test was sent to {$data['recipient']}.")
                    ->success()
                    ->send();
            });
    }
}

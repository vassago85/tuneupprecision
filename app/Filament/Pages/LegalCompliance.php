<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\LegalIdentity;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LegalCompliance extends Page
{
    protected string $view = 'filament.pages.legal-compliance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Legal & Compliance';

    protected static ?string $title = 'Legal & Compliance';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(LegalIdentity::editable());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Effective identity')
                    ->description('Saved here and shown on the public legal pages, quotes and invoices. Leave a field blank to fall back to .env. Telephone, email, VAT and dealer licence stay in sync with Settings.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('legal_name')
                            ->label('Legal name')
                            ->maxLength(255),
                        TextInput::make('trading_as')
                            ->label('Trading as')
                            ->maxLength(255),
                        TextInput::make('legal_status')
                            ->label('Legal status')
                            ->maxLength(255)
                            ->placeholder('Private company'),
                        TextInput::make('registration_no')
                            ->label('Registration number')
                            ->maxLength(255),
                        TextInput::make('vat_no')
                            ->label('VAT number')
                            ->maxLength(255),
                        TextInput::make('dealer_licence_no')
                            ->label('Dealer licence')
                            ->maxLength(255),
                        TextInput::make('legal_email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('legal_phone')
                            ->label('Telephone')
                            ->maxLength(255),
                        Textarea::make('office_bearers')
                            ->label('Office bearers')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('physical_address')
                            ->label('Physical address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('postal_address')
                            ->label('Postal address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $legacyByLegalKey = array_flip(LegalIdentity::legacyBusinessKeys());

        foreach (LegalIdentity::requiredKeys() as $key) {
            $value = trim((string) ($data[$key] ?? ''));
            $stored = $value === '' ? null : $value;

            Setting::put('legal.'.$key, $stored);

            $legacyKey = $legacyByLegalKey[$key] ?? null;
            if (is_string($legacyKey)) {
                Setting::put('business.'.$legacyKey, $stored);
            }
        }

        Notification::make()
            ->title('Legal identity saved')
            ->success()
            ->send();
    }

    /**
     * @return list<string>
     */
    public function getFailures(): array
    {
        return LegalIdentity::failures();
    }

    /**
     * @return array<string, string>
     */
    public function getUpdated(): array
    {
        return config('legal.updated', []);
    }
}

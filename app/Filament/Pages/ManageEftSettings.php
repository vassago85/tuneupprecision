<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\BusinessDetails;
use App\Support\Eft;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageEftSettings extends Page
{
    protected string $view = 'filament.pages.manage-eft-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'EFT & reference settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        // Load the currently-effective EFT details (saved values, else env defaults).
        $this->form->fill([
            ...Eft::details(),
            ...BusinessDetails::details(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('EFT bank details')
                    ->description('Where guests pay for bookings and orders. Saved here and used at checkout — editable without a deploy.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('account_name')
                            ->label('Account name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('account_number')
                            ->label('Account number')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('branch_code')
                            ->label('Branch code')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Business details')
                    ->description('Letterhead on rifle-build quotations. Editable without a deploy.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tel')
                            ->label('Telephone')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Quote email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('vat_number')
                            ->label('VAT number')
                            ->maxLength(255),
                        TextInput::make('dealer_number')
                            ->label('Dealer number')
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (Eft::keys() as $key) {
            Setting::put("eft.{$key}", $data[$key] ?? null);
        }

        foreach (BusinessDetails::keys() as $key) {
            Setting::put("business.{$key}", $data[$key] ?? null);
        }

        Notification::make()
            ->title('EFT settings saved')
            ->success()
            ->send();
    }

    /**
     * Reference formats are code-driven (see App\Support\HasReference), so they
     * remain read-only here.
     *
     * @return array<string, string>
     */
    public function getReferences(): array
    {
        return config('tuneup.references');
    }
}

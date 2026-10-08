<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Pages\ManageMailSettings;
use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Models\EmailLog;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;

    public function getSubheading(): ?string
    {
        return 'Every email the site has handed to the mail provider in the last '.EmailLog::KEEP_DAYS.' days. '
            .'"Sent" means Mailgun accepted it; if one never arrives, look up its message ID in the Mailgun logs.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label('Email settings')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->color('gray')
                ->url(ManageMailSettings::getUrl()),
        ];
    }
}

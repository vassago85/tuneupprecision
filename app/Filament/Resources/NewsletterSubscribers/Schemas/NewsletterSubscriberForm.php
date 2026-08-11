<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use App\Enums\SubscriberStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('name')
                    ->maxLength(255),
                Select::make('status')
                    ->options(SubscriberStatus::class)
                    ->default(SubscriberStatus::Subscribed)
                    ->required(),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Enums\TestimonialSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_type_id')
                    ->label('Discipline')
                    ->relationship('trainingType', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Which training discipline this testimonial is for.'),
                Select::make('training_event_id')
                    ->label('Specific event')
                    ->relationship('trainingEvent', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => trim(
                        ($record->courseTemplate?->title ?? $record->displayTitle() ?? 'Event')
                        .' · '.optional($record->starts_on)->format('d M Y')
                    ))
                    ->searchable()
                    ->preload()
                    ->helperText('Optional — pin to a specific date if you want it on the card.'),
                TextInput::make('author_name')
                    ->label('Name and surname')
                    ->required()
                    ->maxLength(120),
                TextInput::make('author_email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->helperText('Private. Used to email the shooter a copy. Never shown on the site.'),
                Textarea::make('body')
                    ->label('Testimonial')
                    ->required()
                    ->rows(5)
                    ->minLength(10)
                    ->maxLength(800)
                    ->columnSpanFull(),
                Select::make('source')
                    ->options(TestimonialSource::class)
                    ->default(TestimonialSource::Manual)
                    ->required()
                    ->helperText('"Added by admin" for backfilled testimonials; "From shooter" when the shooter submitted via the public form.'),
                Toggle::make('is_approved')
                    ->label('Approved (visible on the site)')
                    ->default(false)
                    ->helperText('Approved testimonials appear in the homepage carousel.'),
            ]);
    }
}

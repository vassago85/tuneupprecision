<?php

declare(strict_types=1);

namespace App\Filament\Resources\Testimonials\Tables;

use App\Enums\TestimonialSource;
use App\Models\Testimonial;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Pending-first: unapproved rows bubble to the top so Dirk sees
            // work waiting on him without touching the filter.
            ->defaultSort('is_approved', 'asc')
            ->columns([
                TextColumn::make('author_name')
                    ->label('Name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('author_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('trainingType.name')
                    ->label('Discipline')
                    ->badge()
                    ->searchable(),
                TextColumn::make('trainingEvent.starts_on')
                    ->label('Event date')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('body')
                    ->label('Snippet')
                    ->formatStateUsing(fn (string $state): string => Str::limit($state, 90))
                    ->wrap(),
                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),
                TextColumn::make('source')
                    ->badge(),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->label('Approved at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approval status')
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->placeholder('All'),
                SelectFilter::make('source')
                    ->options(TestimonialSource::class),
                SelectFilter::make('training_type_id')
                    ->label('Discipline')
                    ->relationship('trainingType', 'name'),
            ])
            ->recordActions([
                self::approveAction(),
                self::unapproveAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::bulkApproveAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (Testimonial $record): bool => ! $record->is_approved)
            ->requiresConfirmation()
            ->modalHeading('Publish this testimonial?')
            ->modalDescription('It will appear in the homepage carousel immediately.')
            ->action(function (Testimonial $record): void {
                $record->update([
                    'is_approved' => true,
                    'approved_at' => now(),
                ]);

                Notification::make()
                    ->title('Testimonial published')
                    ->success()
                    ->send();
            });
    }

    private static function unapproveAction(): Action
    {
        return Action::make('unapprove')
            ->label('Unpublish')
            ->icon(Heroicon::OutlinedEyeSlash)
            ->color('gray')
            ->visible(fn (Testimonial $record): bool => $record->is_approved)
            ->requiresConfirmation()
            ->action(function (Testimonial $record): void {
                $record->update([
                    'is_approved' => false,
                    'approved_at' => null,
                ]);

                Notification::make()
                    ->title('Testimonial hidden')
                    ->success()
                    ->send();
            });
    }

    private static function bulkApproveAction(): BulkAction
    {
        return BulkAction::make('approve')
            ->label('Approve selected')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $count = 0;
                foreach ($records as $record) {
                    if (! $record->is_approved) {
                        $record->update([
                            'is_approved' => true,
                            'approved_at' => now(),
                        ]);
                        $count++;
                    }
                }

                Notification::make()
                    ->title($count === 1 ? '1 testimonial published' : "{$count} testimonials published")
                    ->success()
                    ->send();
            });
    }
}

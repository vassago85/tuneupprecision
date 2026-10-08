<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Models\EmailLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d M Y H:i')
                    ->description(fn (EmailLog $record): string => $record->created_at->diffForHumans())
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, EmailLog $record): string => match (true) {
                        $state === EmailLog::FAILED => 'Failed',
                        $record->mailer === 'log' => 'Log only',
                        default => 'Sent',
                    })
                    ->color(fn (string $state, EmailLog $record): string => match (true) {
                        $state === EmailLog::FAILED => 'danger',
                        $record->mailer === 'log' => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn (EmailLog $record): ?string => $record->mailer === 'log' && $record->status === EmailLog::SENT
                        ? 'The mail driver was set to Log, so this was written to the log file, not delivered.'
                        : null),
                TextColumn::make('recipients')
                    ->label('To')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('subject')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—')
                    ->description(fn (EmailLog $record): ?string => $record->error ? Str::limit($record->error, 160) : null)
                    ->tooltip(fn (EmailLog $record): ?string => $record->error),
                TextColumn::make('mailable')
                    ->label('Type')
                    ->formatStateUsing(fn (EmailLog $record): string => $record->typeLabel())
                    ->placeholder('Plain message')
                    ->badge()
                    ->color('gray')
                    ->visibleFrom('md'),
                TextColumn::make('mailer')
                    ->label('Via')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('message_id')
                    ->label('Message ID')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        EmailLog::SENT => 'Sent',
                        EmailLog::FAILED => 'Failed',
                    ]),
                SelectFilter::make('mailable')
                    ->label('Type')
                    ->options(fn (): array => EmailLog::query()
                        ->whereNotNull('mailable')
                        ->distinct()
                        ->pluck('mailable')
                        ->mapWithKeys(fn (string $class): array => [$class => (new EmailLog(['mailable' => $class]))->typeLabel()])
                        ->sort()
                        ->all()),
            ]);
    }
}

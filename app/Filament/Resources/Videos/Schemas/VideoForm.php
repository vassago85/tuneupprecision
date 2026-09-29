<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\TrainingType;
use App\Support\YoutubeId;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('training_type_id')
                    ->label('Discipline')
                    ->options(fn (): array => TrainingType::query()
                        ->activeOrdered()
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->placeholder('Uncategorised')
                    ->helperText('Which discipline tab this video appears under on The Range.'),
                Textarea::make('caption')
                    ->rows(2)
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->helperText('Optional one-liner shown under the title.'),
                TextInput::make('youtube_id')
                    ->label('YouTube link')
                    ->maxLength(255)
                    ->placeholder('https://www.youtube.com/watch?v=…')
                    ->helperText('Paste the full YouTube URL, or just the 11-character video ID. Ignored when a video file is uploaded below.')
                    ->dehydrateStateUsing(fn (?string $state): ?string => YoutubeId::extract($state))
                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                        if (filled($value) && YoutubeId::extract(is_string($value) ? $value : null) === null) {
                            $fail('Paste a YouTube link or an 11-character video ID.');
                        }
                    }),
                SpatieMediaLibraryFileUpload::make('poster')
                    ->label('Custom poster (optional)')
                    ->collection('poster')
                    ->image()
                    ->imageResizeMode('contain')
                    ->imageResizeUpscale(false)
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('900')
                    ->helperText('Falls back to the YouTube thumbnail if not set.'),
                SpatieMediaLibraryFileUpload::make('file')
                    ->label('Upload video (optional)')
                    ->collection('file')
                    ->disk('public')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                    ->maxSize(512 * 1024) // 512 MB — must stay within nginx, PHP, and Livewire limits
                    ->helperText('MP4, WebM, or MOV, up to 512 MB. If set, this plays instead of YouTube.'),
                Toggle::make('is_featured')
                    ->label('Featured video')
                    ->helperText('Shown at the top of The Range page. Only the newest featured wins.'),
                Toggle::make('is_members_only')
                    ->label('Members only')
                    ->helperText('Only visible to Dirk-verified members.'),
                Toggle::make('is_active')
                    ->label('Published')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first within a discipline.'),
            ]);
    }
}

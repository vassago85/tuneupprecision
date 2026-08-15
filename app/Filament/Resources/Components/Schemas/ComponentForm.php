<?php

declare(strict_types=1);

namespace App\Filament\Resources\Components\Schemas;

use App\Models\ComponentCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('component_category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->live()
                    ->searchable()
                    ->preload(),
                TextInput::make('brand')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TagsInput::make('specs')
                    ->helperText('Up to 3 short spec lines shown on the card.')
                    ->columnSpanFull(),
                TextInput::make('price_cents')
                    ->label('Retail incl. VAT')
                    ->prefix('R')
                    ->numeric()
                    ->required()
                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                TextInput::make('cost_cents')
                    ->label('Cost incl. VAT')
                    ->prefix('R')
                    ->numeric()
                    ->required()
                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                TextInput::make('lead_time_weeks')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                TextInput::make('footprint')
                    ->visible(fn (Get $get): bool => self::categoryKey($get) === 'barrelled' || self::categoryKey($get) === 'action')
                    ->helperText('rem700, tikka, or ruger'),
                TagsInput::make('fits_footprints')
                    ->visible(fn (Get $get): bool => self::categoryKey($get) === 'chassis')
                    ->helperText('Footprints this chassis accepts.'),
                TextInput::make('tube_diameter')
                    ->visible(fn (Get $get): bool => self::categoryKey($get) === 'optic')
                    ->helperText('30, 34, or 36'),
                TagsInput::make('fits_tube_diameters')
                    ->visible(fn (Get $get): bool => self::categoryKey($get) === 'mount')
                    ->helperText('Tube sizes this mount accepts.'),
                FileUpload::make('image_path')
                    ->image()
                    ->directory('components')
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('2000')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Published')
                    ->default(true),
                Toggle::make('is_automatic')
                    ->label('Automatic labour')
                    ->helperText('Hidden from the picker; inserted by the build service.'),
                Toggle::make('allows_quantity')
                    ->label('Quantity stepper'),
            ]);
    }

    protected static function categoryKey(Get $get): ?string
    {
        $id = $get('component_category_id');
        if (! $id) {
            return null;
        }

        return ComponentCategory::query()->whereKey($id)->value('key');
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Components\Actions;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportComponentsAction
{
    public static function make(): Action
    {
        return Action::make('importCatalogue')
            ->label('Import CSV / JSON')
            ->icon('heroicon-o-arrow-up-tray')
            ->schema([
                FileUpload::make('file')
                    ->acceptedFileTypes(['application/json', 'text/csv', 'text/plain'])
                    ->required()
                    ->storeFiles(false),
            ])
            ->action(function (array $data): void {
                $file = $data['file'];
                $contents = is_string($file)
                    ? (Storage::disk('local')->get($file) ?? file_get_contents($file))
                    : $file->get();

                $imported = str_contains((string) ($file->getClientOriginalName() ?? ''), '.csv')
                    || str_starts_with(ltrim((string) $contents), 'category,')
                    ? self::fromCsv((string) $contents)
                    : self::fromJson((string) $contents);

                Notification::make()
                    ->title("Imported {$imported} components")
                    ->success()
                    ->send();
            });
    }

    protected static function fromJson(string $contents): int
    {
        $payload = json_decode($contents, true);
        if (! is_array($payload)) {
            return 0;
        }

        $count = 0;
        foreach ($payload as $rawKey => $items) {
            $key = self::mapCategoryKey((string) $rawKey);
            $category = ComponentCategory::query()->where('key', $key)->first();
            if ($category === null || ! is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                self::upsert($category, $item);
                $count++;
            }
        }

        return $count;
    }

    protected static function fromCsv(string $contents): int
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($contents)) ?: [];
        $header = str_getcsv(array_shift($lines) ?: '');
        $count = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = array_combine($header, str_getcsv($line));
            if ($row === false) {
                continue;
            }
            $key = self::mapCategoryKey((string) ($row['category'] ?? $row['key'] ?? ''));
            $category = ComponentCategory::query()->where('key', $key)->first();
            if ($category === null) {
                continue;
            }
            self::upsert($category, $row);
            $count++;
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected static function upsert(ComponentCategory $category, array $item): void
    {
        $name = (string) ($item['name'] ?? '');
        $brand = (string) ($item['brand'] ?? '');
        $slug = (string) ($item['slug'] ?? Str::slug($brand.' '.$name));

        $price = $item['price'] ?? $item['price_cents'] ?? 0;
        $cost = $item['cost'] ?? $item['cost_cents'] ?? 0;
        $priceCents = isset($item['price_cents']) ? (int) $item['price_cents'] : Money::toCents($price);
        $costCents = isset($item['cost_cents']) ? (int) $item['cost_cents'] : Money::toCents($cost);

        $fits = $item['fits'] ?? $item['fits_footprints'] ?? null;
        $tube = $item['tube'] ?? $item['tube_diameter'] ?? $item['fits_tube_diameters'] ?? null;

        Component::updateOrCreate(
            ['slug' => $slug],
            [
                'component_category_id' => $category->id,
                'brand' => $brand,
                'name' => $name,
                'specs' => $item['specs'] ?? [],
                'price_cents' => $priceCents,
                'cost_cents' => $costCents,
                'footprint' => in_array($category->key, ['barrelled', 'action'], true)
                    ? (is_array($fits) ? ($fits[0] ?? null) : ($item['footprint'] ?? null))
                    : ($item['footprint'] ?? null),
                'fits_footprints' => $category->key === 'chassis'
                    ? (array) ($fits ?? [])
                    : null,
                'tube_diameter' => $category->key === 'optic'
                    ? (is_array($tube) ? ($tube[0] ?? null) : $tube)
                    : null,
                'fits_tube_diameters' => $category->key === 'mount'
                    ? (array) ($tube ?? [])
                    : null,
                'is_active' => true,
            ],
        );
    }

    protected static function mapCategoryKey(string $key): string
    {
        return match ($key) {
            'chassisacc' => 'chassis_accessory',
            'extras' => 'extra',
            default => $key,
        };
    }
}

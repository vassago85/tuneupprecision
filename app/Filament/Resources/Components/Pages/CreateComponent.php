<?php

declare(strict_types=1);

namespace App\Filament\Resources\Components\Pages;

use App\Filament\Resources\Components\ComponentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComponent extends CreateRecord
{
    protected static string $resource = ComponentResource::class;
}

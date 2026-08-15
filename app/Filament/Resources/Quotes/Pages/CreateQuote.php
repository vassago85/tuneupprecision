<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Pages;

use App\Enums\QuoteStatus;
use App\Filament\Resources\Quotes\QuoteResource;
use App\RifleBuilder\BuildSelection;
use App\Services\RifleBuildService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    /**
     * @var array<string, mixed>
     */
    public array $buildSelection = [];

    #[On('rifle-build-changed')]
    public function onBuildChanged(array $selection): void
    {
        $this->buildSelection = $selection;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $selection = BuildSelection::fromArray([
            ...$this->buildSelection,
            'platform' => $data['platform'] ?? $this->buildSelection['platform'] ?? 'barrelled',
            'discount_amount_cents' => $data['discount_amount_cents'] ?? 0,
            'deposit_percent' => $data['deposit_percent'] ?? 50,
        ]);

        return app(RifleBuildService::class)->persistQuote($selection, [
            'status' => QuoteStatus::Draft,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'licence_status' => $data['licence_status'] ?? null,
            'lead_time' => $data['lead_time'] ?? null,
            'notes' => $data['notes'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}

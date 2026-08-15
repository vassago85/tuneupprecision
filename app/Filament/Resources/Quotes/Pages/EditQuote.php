<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Pages;

use App\Actions\AcceptQuote;
use App\Actions\IssueQuote;
use App\Enums\QuoteStatus;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Quote;
use App\RifleBuilder\BuildSelection;
use App\Services\RifleBuildService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    /**
     * @var array<string, mixed>
     */
    public array $buildSelection = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->buildSelection = app(RifleBuildService::class)
            ->selectionFromQuote($this->getRecord())
            ->toArray();
    }

    #[On('rifle-build-changed')]
    public function onBuildChanged(array $selection): void
    {
        $this->buildSelection = $selection;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Quote $record */
        $selection = BuildSelection::fromArray([
            ...$this->buildSelection,
            'platform' => $data['platform'] ?? $this->buildSelection['platform'] ?? $record->platform->value,
            'discount_amount_cents' => $data['discount_amount_cents'] ?? 0,
            'deposit_percent' => $data['deposit_percent'] ?? $record->deposit_percent,
        ]);

        $record = app(RifleBuildService::class)->syncQuote($record, $selection);

        $record->update([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'licence_status' => $data['licence_status'] ?? null,
            'lead_time' => $data['lead_time'] ?? $record->lead_time,
            'notes' => $data['notes'] ?? null,
            'valid_until' => $data['valid_until'] ?? $record->valid_until,
        ]);

        return $record->fresh('lines');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): StreamedResponse {
                    $quote = $this->getRecord();
                    $pdf = app(IssueQuote::class)->pdf($quote);

                    return response()->streamDownload(
                        fn () => print $pdf->output(),
                        $quote->reference.'.pdf',
                        ['Content-Type' => 'application/pdf'],
                    );
                }),
            Action::make('emailQuote')
                ->label('Email quote')
                ->icon('heroicon-o-envelope')
                ->requiresConfirmation()
                ->action(function (): void {
                    app(IssueQuote::class)->email($this->getRecord());
                    Notification::make()->title('Quote emailed')->success()->send();
                }),
            Action::make('duplicate')
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->action(function () {
                    $original = $this->getRecord()->load('lines');
                    $copy = $original->replicate(['reference']);
                    $copy->status = QuoteStatus::Draft;
                    $copy->created_by = auth()->id();
                    $copy->reference = null;
                    $copy->save();

                    foreach ($original->lines as $line) {
                        $copy->lines()->create($line->only([
                            'component_id', 'group_label', 'brand', 'description',
                            'specs', 'quantity', 'unit_price_cents', 'line_total_cents',
                            'unit_cost_cents', 'sort_order',
                        ]));
                    }

                    Notification::make()->title('Duplicated as '.$copy->reference)->success()->send();

                    return redirect(QuoteResource::getUrl('edit', ['record' => $copy]));
                }),
            Action::make('accept')
                ->label('Mark accepted')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (): bool => ! in_array($this->getRecord()->status, [QuoteStatus::Accepted, QuoteStatus::Converted], true))
                ->requiresConfirmation()
                ->action(function (): void {
                    app(AcceptQuote::class)->handle($this->getRecord());
                    Notification::make()->title('Quote accepted — deposit payment created')->success()->send();
                }),
            DeleteAction::make(),
        ];
    }
}

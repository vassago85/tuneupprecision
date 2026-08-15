<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Mail\BuildEnquiry;
use App\Models\RifleBuildShare;
use App\RifleBuilder\BuildSelection;
use App\Services\RifleBuildService;
use App\Support\BusinessDetails;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.rifle-builder')]
class RifleBuilder extends Component
{
    public ?string $shareCode = null;

    public string $customerName = '';

    public string $customerEmail = '';

    public string $customerPhone = '';

    public string $message = '';

    public bool $showRequest = false;

    public ?string $toast = null;

    public ?string $submittedReference = null;

    /**
     * @var array<string, mixed>
     */
    public array $buildSelection = [];

    public function mount(?string $code = null): void
    {
        if ($code) {
            $share = RifleBuildShare::query()->where('code', $code)->first();
            if ($share) {
                $this->shareCode = $share->code;
                $this->buildSelection = $share->payload;
            }
        }
    }

    #[On('rifle-build-changed')]
    public function onBuildChanged(array $selection): void
    {
        $this->buildSelection = $selection;
    }

    public function shareBuild(): void
    {
        $share = RifleBuildShare::create([
            'payload' => $this->currentSelection()->toArray(),
        ]);

        $this->shareCode = $share->code;
        $this->toast = 'Shareable link ready: '.url('/rifle-builder/'.$share->code);
    }

    public function openRequest(): void
    {
        $this->showRequest = true;
    }

    public function submitRequest(): void
    {
        $this->validate([
            'customerName' => ['required', 'string', 'max:255'],
            'customerEmail' => ['required', 'email:rfc', 'max:255'],
            'customerPhone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $selection = $this->currentSelection();
        $result = app(RifleBuildService::class)->evaluate($selection);

        if ($result->needsTriggerChoice) {
            $this->showRequest = false;
            $this->toast = 'Pick an aftermarket trigger before requesting this build.';

            return;
        }

        $quote = app(RifleBuildService::class)->persistQuote($selection, [
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone ?: null,
            'notes' => $this->message ?: null,
            'created_by' => null,
        ]);

        $quote->load('lines');

        $dirk = BusinessDetails::details()['email'] ?? config('tuneup.mail.from_address');
        Mail::to($dirk)->queue(new BuildEnquiry($quote, false));
        Mail::to($quote->customer_email)->queue(new BuildEnquiry($quote, true));

        $this->submittedReference = $quote->reference;
        $this->showRequest = false;
        $this->toast = 'Request sent · '.$quote->reference;
    }

    public function render()
    {
        $result = app(RifleBuildService::class)->evaluate($this->currentSelection());

        return view('livewire.rifle-builder', [
            'result' => $result,
            'business' => BusinessDetails::details(),
        ]);
    }

    protected function currentSelection(): BuildSelection
    {
        return BuildSelection::fromArray($this->buildSelection ?: ['platform' => 'barrelled']);
    }
}

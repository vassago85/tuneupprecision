<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTestimonial extends EditRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Keep `approved_at` in sync with the toggle so the sort/badge is right.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['is_approved'] ?? false) && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }

        if (! ($data['is_approved'] ?? false)) {
            $data['approved_at'] = null;
        }

        return $data;
    }
}

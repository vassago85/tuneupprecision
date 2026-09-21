<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    protected static string $resource = TestimonialResource::class;

    /**
     * If the admin ticks "approved" when creating the row, stamp `approved_at`
     * automatically so the sort/badge stays in sync.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['is_approved'] ?? false) && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }

        return $data;
    }
}

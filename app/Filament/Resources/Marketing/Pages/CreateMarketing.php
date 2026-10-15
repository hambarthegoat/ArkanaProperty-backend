<?php

namespace App\Filament\Resources\Marketing\Pages;

use App\Filament\Resources\Marketing\MarketingResource;
use App\Support\Phone;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketing extends CreateRecord
{
    protected static string $resource = MarketingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'marketing';
        $data['no_telp'] = filled($data['no_telp'] ?? null) ? Phone::normalize($data['no_telp']) : null;

        return $data;
    }
}

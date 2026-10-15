<?php

namespace App\Filament\Resources\Marketing\Pages;

use App\Filament\Resources\Marketing\MarketingResource;
use App\Support\Phone;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketing extends EditRecord
{
    protected static string $resource = MarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['no_telp'] = filled($data['no_telp'] ?? null) ? Phone::normalize($data['no_telp']) : null;

        return $data;
    }
}

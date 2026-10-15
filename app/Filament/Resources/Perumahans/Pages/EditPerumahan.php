<?php

namespace App\Filament\Resources\Perumahans\Pages;

use App\Filament\Resources\Perumahans\PerumahanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPerumahan extends EditRecord
{
    protected static string $resource = PerumahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Kavlings\Pages;

use App\Filament\Resources\Kavlings\KavlingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKavling extends EditRecord
{
    protected static string $resource = KavlingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

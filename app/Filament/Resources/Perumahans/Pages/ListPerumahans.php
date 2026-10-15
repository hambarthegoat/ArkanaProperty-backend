<?php

namespace App\Filament\Resources\Perumahans\Pages;

use App\Filament\Resources\Perumahans\PerumahanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerumahans extends ListRecords
{
    protected static string $resource = PerumahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

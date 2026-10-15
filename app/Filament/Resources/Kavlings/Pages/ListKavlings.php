<?php

namespace App\Filament\Resources\Kavlings\Pages;

use App\Filament\Resources\Kavlings\KavlingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKavlings extends ListRecords
{
    protected static string $resource = KavlingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

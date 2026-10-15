<?php

namespace App\Filament\Resources\Marketing\Pages;

use App\Filament\Resources\Marketing\MarketingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketing extends ListRecords
{
    protected static string $resource = MarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

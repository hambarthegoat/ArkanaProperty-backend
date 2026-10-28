<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\Actions\LeadActions;
use App\Filament\Resources\Leads\LeadsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeads extends EditRecord
{
    protected static string $resource = LeadsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LeadActions::assign()->after(fn () => $this->refreshFormData(['id_marketing'])),
            LeadActions::validate()->after(fn () => $this->refreshFormData(['status_keseriusan', 'id_marketing', 'prioritas'])),
            LeadActions::reject()->after(fn () => $this->refreshFormData(['status_keseriusan'])),
            LeadActions::downloadKtp(),
            DeleteAction::make(),
        ];
    }
}

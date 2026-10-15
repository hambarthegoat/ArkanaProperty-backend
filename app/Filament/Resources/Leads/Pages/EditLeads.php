<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\Actions\LeadActions;
use App\Filament\Resources\Leads\LeadsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditLeads extends EditRecord
{
    protected static string $resource = LeadsResource::class;

    protected ?string $previousKtp = null;

    protected function getHeaderActions(): array
    {
        return [
            LeadActions::validate()->after(fn () => $this->refreshFormData(['status_keseriusan', 'id_marketing', 'prioritas'])),
            LeadActions::reject()->after(fn () => $this->refreshFormData(['status_keseriusan'])),
            LeadActions::downloadKtp(),
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->previousKtp = $this->record->getOriginal('file_ktp');
    }

    protected function afterSave(): void
    {
        if ($this->previousKtp && $this->previousKtp !== $this->record->file_ktp) {
            Storage::disk('local')->delete($this->previousKtp);
        }
    }
}

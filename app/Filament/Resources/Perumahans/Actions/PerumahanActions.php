<?php

namespace App\Filament\Resources\Perumahans\Actions;

use App\Models\Perumahan;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;

/**
 * Deleting a project cascades to its house types, units and leads, so the
 * panel refuses it under the same rules as the admin API.
 */
class PerumahanActions
{
    public static function delete(): DeleteAction
    {
        return DeleteAction::make()
            ->before(function (DeleteAction $action, Perumahan $record) {
                if ($blocker = $record->deletionBlocker()) {
                    Notification::make()->title('Perumahan tidak bisa dihapus')->body($blocker)->danger()->send();
                    $action->halt();
                }
            })
            ->after(fn (Perumahan $record) => Perumahan::deleteGambarFile($record->gambar));
    }
}

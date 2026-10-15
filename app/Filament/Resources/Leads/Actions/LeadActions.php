<?php

namespace App\Filament\Resources\Leads\Actions;

use App\Enums\LeadStatus;
use App\Exceptions\LeadValidationException;
use App\Models\Akun;
use App\Models\Leads;
use App\Services\LeadValidationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

/**
 * Record actions shared by the leads table and edit page. Validation goes
 * through LeadValidationService, same as the admin API.
 */
class LeadActions
{
    public static function validate(): Action
    {
        return Action::make('validasi')
            ->label('Validasi')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->visible(fn (Leads $record) => $record->status_keseriusan !== LeadStatus::Validated)
            ->modalHeading('Validasi KTP & tugaskan marketing')
            ->modalDescription('Akun portal konsumen dibuat dengan username = nomor HP. Password hanya ditampilkan sekali.')
            ->schema([
                Select::make('id_marketing')
                    ->label('Marketing')
                    ->options(fn () => Akun::where('role', 'marketing')->orderBy('nama')->get()
                        ->mapWithKeys(fn (Akun $akun) => [$akun->id => $akun->nama ?? $akun->username]))
                    ->required(),
            ])
            ->action(function (Leads $record, array $data, Action $action) {
                try {
                    $result = app(LeadValidationService::class)->validate($record, Akun::findOrFail($data['id_marketing']));
                } catch (LeadValidationException $e) {
                    Notification::make()->title('Validasi gagal')->body($e->getMessage())->danger()->send();
                    $action->halt();
                }

                $kredensial = $result['kredensial'];

                Notification::make()
                    ->title('Lead tervalidasi')
                    ->body($kredensial['akun_baru']
                        ? "Akun portal dibuat. Username: {$kredensial['username']} — Password: {$kredensial['password']}. Kirimkan ke konsumen, password tidak akan ditampilkan lagi."
                        : "Lead dihubungkan ke akun portal yang sudah ada ({$kredensial['username']}).")
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    public static function reject(): Action
    {
        return Action::make('tolak')
            ->label('Tolak')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Leads $record) => $record->status_keseriusan === LeadStatus::Pending)
            ->requiresConfirmation()
            ->action(function (Leads $record) {
                try {
                    app(LeadValidationService::class)->reject($record);
                } catch (LeadValidationException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();

                    return;
                }

                Notification::make()->title('Lead ditolak')->success()->send();
            });
    }

    public static function downloadKtp(): Action
    {
        return Action::make('ktp')
            ->label('Unduh KTP')
            ->icon(Heroicon::OutlinedIdentification)
            ->color('gray')
            ->visible(fn (Leads $record) => $record->hasKtp())
            ->action(fn (Leads $record) => Storage::disk('local')->download($record->file_ktp));
    }
}

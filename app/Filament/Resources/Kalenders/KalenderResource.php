<?php

namespace App\Filament\Resources\Kalenders;

use App\Filament\Resources\Kalenders\Pages\ListKalenders;
use App\Filament\Resources\Kalenders\Tables\KalendersTable;
use App\Models\Kalender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Read-only overview of agenda that marketing schedules from the frontend.
 */
class KalenderResource extends Resource
{
    protected static ?string $model = Kalender::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $modelLabel = 'agenda';

    protected static ?string $pluralModelLabel = 'agenda';

    protected static ?string $slug = 'agenda';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return KalendersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKalenders::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ReportMarketings;

use App\Filament\Resources\ReportMarketings\Pages\ListReportMarketings;
use App\Filament\Resources\ReportMarketings\Tables\ReportMarketingsTable;
use App\Models\ReportMarketing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Read-only: reports are written by marketing from the frontend.
 */
class ReportMarketingResource extends Resource
{
    protected static ?string $model = ReportMarketing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Report Marketing';

    protected static ?string $modelLabel = 'report marketing';

    protected static ?string $pluralModelLabel = 'report marketing';

    protected static ?string $slug = 'reports';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ReportMarketingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportMarketings::route('/'),
        ];
    }
}

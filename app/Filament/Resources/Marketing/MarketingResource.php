<?php

namespace App\Filament\Resources\Marketing;

use App\Filament\Resources\Marketing\Pages\CreateMarketing;
use App\Filament\Resources\Marketing\Pages\EditMarketing;
use App\Filament\Resources\Marketing\Pages\ListMarketing;
use App\Filament\Resources\Marketing\Schemas\MarketingForm;
use App\Filament\Resources\Marketing\Tables\MarketingTable;
use App\Models\Akun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Marketing accounts are tb_akun rows with role "marketing".
 */
class MarketingResource extends Resource
{
    protected static ?string $model = Akun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Akun Marketing';

    protected static ?string $modelLabel = 'akun marketing';

    protected static ?string $pluralModelLabel = 'akun marketing';

    protected static ?string $slug = 'marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'marketing');
    }

    public static function form(Schema $schema): Schema
    {
        return MarketingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketing::route('/'),
            'create' => CreateMarketing::route('/create'),
            'edit' => EditMarketing::route('/{record}/edit'),
        ];
    }
}

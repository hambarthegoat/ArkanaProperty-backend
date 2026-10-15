<?php

namespace App\Filament\Resources\Perumahans;

use App\Filament\Resources\Perumahans\Pages\CreatePerumahan;
use App\Filament\Resources\Perumahans\Pages\EditPerumahan;
use App\Filament\Resources\Perumahans\Pages\ListPerumahans;
use App\Filament\Resources\Perumahans\RelationManagers\TipeRumahRelationManager;
use App\Filament\Resources\Perumahans\Schemas\PerumahanForm;
use App\Filament\Resources\Perumahans\Tables\PerumahansTable;
use App\Models\Perumahan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PerumahanResource extends Resource
{
    protected static ?string $model = Perumahan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Properti';

    protected static ?string $navigationLabel = 'Perumahan';

    protected static ?string $modelLabel = 'perumahan';

    protected static ?string $pluralModelLabel = 'perumahan';

    protected static ?string $slug = 'perumahan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_perumahan';

    public static function form(Schema $schema): Schema
    {
        return PerumahanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerumahansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withUnitCounts()->withCount('tipeRumah');
    }

    public static function getRelations(): array
    {
        return [
            TipeRumahRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerumahans::route('/'),
            'create' => CreatePerumahan::route('/create'),
            'edit' => EditPerumahan::route('/{record}/edit'),
        ];
    }
}

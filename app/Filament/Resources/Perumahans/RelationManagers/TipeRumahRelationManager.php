<?php

namespace App\Filament\Resources\Perumahans\RelationManagers;

use App\Enums\JenisRumah;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipeRumahRelationManager extends RelationManager
{
    protected static string $relationship = 'tipeRumah';

    protected static ?string $title = 'Tipe Rumah';

    protected static ?string $modelLabel = 'tipe rumah';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_tipe')
                    ->label('Nama Tipe')
                    ->required()
                    ->placeholder('Tipe 36/60'),
                Select::make('jenis_rumah')
                    ->label('Jenis')
                    ->options(JenisRumah::class)
                    ->required(),
                TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('dp')
                    ->label('DP')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('cicilan')
                    ->label('Cicilan / bulan')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('luas_tanah')
                    ->numeric()
                    ->suffix('m²'),
                TextInput::make('luas_bangunan')
                    ->numeric()
                    ->suffix('m²'),
                TextInput::make('kamar_tidur')
                    ->numeric(),
                TextInput::make('kamar_mandi')
                    ->numeric(),
                FileUpload::make('gambar')
                    ->image()
                    ->disk('public')
                    ->directory('tipe-rumah')
                    ->maxSize(5120),
                Textarea::make('spesifikasi')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_tipe')
            ->modifyQueryUsing(fn ($query) => $query->withCount('kavling'))
            ->columns([
                TextColumn::make('nama_tipe')
                    ->label('Tipe')
                    ->searchable(),
                TextColumn::make('jenis_rumah')
                    ->label('Jenis')
                    ->badge(),
                TextColumn::make('harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('dp')
                    ->label('DP')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('cicilan')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('luas_bangunan')
                    ->label('LB/LT')
                    ->formatStateUsing(fn ($record) => "{$record->luas_bangunan}/{$record->luas_tanah} m²"),
                TextColumn::make('kavling_count')
                    ->label('Kavling'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Perumahans\Tables;

use App\Enums\PerumahanStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PerumahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar')
                    ->disk('public')
                    ->square(),
                TextColumn::make('nama_perumahan')
                    ->label('Perumahan')
                    ->description(fn ($record) => $record->lokasi)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('tipe_rumah_count')
                    ->label('Tipe'),
                TextColumn::make('total_unit')
                    ->label('Total Unit'),
                TextColumn::make('unit_tersedia')
                    ->label('Tersedia'),
                TextColumn::make('unit_booking')
                    ->label('Booking')
                    ->color('warning'),
                TextColumn::make('unit_terjual')
                    ->label('Terjual')
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PerumahanStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

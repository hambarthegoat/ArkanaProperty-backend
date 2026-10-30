<?php

namespace App\Filament\Resources\Kavlings\Tables;

use App\Enums\KavlingStatus;
use App\Models\Kavling;
use App\Models\Perumahan;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KavlingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tipeRumah.perumahan', 'leads']))
            ->columns([
                TextColumn::make('nomor_kavling')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nomor_rumah')
                    ->label('Rumah')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('blok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipeRumah.perumahan.nama_perumahan')
                    ->label('Perumahan'),
                TextColumn::make('tipeRumah.nama_tipe')
                    ->label('Tipe'),
                SelectColumn::make('status')
                    ->options(KavlingStatus::class)
                    ->selectablePlaceholder(false),
                TextColumn::make('leads.nama_calon')
                    ->label('Pembeli')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->defaultSort('nomor_kavling')
            ->filters([
                SelectFilter::make('perumahan')
                    ->label('Perumahan')
                    ->options(fn () => Perumahan::orderBy('nama_perumahan')->pluck('nama_perumahan', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $id) => $q->whereHas('tipeRumah', fn (Builder $t) => $t->where('id_perumahan', $id)),
                    )),
                SelectFilter::make('status')
                    ->options(KavlingStatus::class),
                SelectFilter::make('blok')
                    ->options(fn () => Kavling::query()->whereNotNull('blok')->distinct()->orderBy('blok')->pluck('blok', 'blok')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

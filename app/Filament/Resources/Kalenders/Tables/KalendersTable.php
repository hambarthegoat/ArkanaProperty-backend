<?php

namespace App\Filament\Resources\Kalenders\Tables;

use App\Enums\AgendaType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KalendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['marketing', 'leadsAkun']))
            ->columns([
                TextColumn::make('tanggal_kegiatan')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('judul')
                    ->searchable(),
                TextColumn::make('jenis')
                    ->badge(),
                TextColumn::make('marketing.nama')
                    ->label('Marketing'),
                TextColumn::make('leadsAkun.nama')
                    ->label('Konsumen'),
                TextColumn::make('lokasi')
                    ->placeholder('-'),
            ])
            ->defaultSort('tanggal_kegiatan')
            ->filters([
                TernaryFilter::make('mendatang')
                    ->label('Waktu')
                    ->placeholder('Semua')
                    ->trueLabel('Mendatang')
                    ->falseLabel('Sudah lewat')
                    ->queries(
                        true: fn (Builder $q) => $q->where('tanggal_kegiatan', '>=', now()),
                        false: fn (Builder $q) => $q->where('tanggal_kegiatan', '<', now()),
                    ),
                SelectFilter::make('jenis')
                    ->options(AgendaType::class),
                SelectFilter::make('id_marketing')
                    ->label('Marketing')
                    ->relationship('marketing', 'nama', fn ($query) => $query->where('role', 'marketing')),
            ]);
    }
}

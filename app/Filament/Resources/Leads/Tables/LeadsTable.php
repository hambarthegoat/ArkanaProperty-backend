<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\Actions\LeadActions;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['perumahan', 'tipeRumah', 'marketing']))
            ->columns([
                TextColumn::make('nama_calon')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('no_telp')
                    ->label('No. WhatsApp')
                    ->searchable(),
                TextColumn::make('perumahan.nama_perumahan')
                    ->label('Perumahan')
                    ->description(fn ($record) => $record->tipeRumah?->nama_tipe),
                TextColumn::make('status_keseriusan')
                    ->label('Status')
                    ->badge(),
                IconColumn::make('file_ktp')
                    ->label('KTP')
                    ->state(fn ($record) => $record->hasKtp())
                    ->boolean(),
                TextColumn::make('marketing.nama')
                    ->label('Marketing')
                    ->placeholder('-'),
                TextColumn::make('prioritas')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status_keseriusan')
                    ->label('Status')
                    ->options(LeadStatus::class),
                SelectFilter::make('id_marketing')
                    ->label('Marketing')
                    ->relationship('marketing', 'nama', fn ($query) => $query->where('role', 'marketing')),
            ])
            ->recordActions([
                LeadActions::assign(),
                LeadActions::validate(),
                ActionGroup::make([
                    EditAction::make()->label('Detail'),
                    LeadActions::reject(),
                    LeadActions::downloadKtp(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}

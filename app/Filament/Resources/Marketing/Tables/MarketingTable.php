<?php

namespace App\Filament\Resources\Marketing\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketingTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['leadsAsMarketing', 'reports']))
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('no_telp')
                    ->label('No. WhatsApp'),
                TextColumn::make('leads_as_marketing_count')
                    ->label('Leads Ditangani'),
                TextColumn::make('reports_count')
                    ->label('Report'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

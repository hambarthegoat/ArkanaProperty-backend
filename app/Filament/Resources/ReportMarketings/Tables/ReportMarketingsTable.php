<?php

namespace App\Filament\Resources\ReportMarketings\Tables;

use App\Enums\ReportActivity;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportMarketingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['marketing', 'leads']))
            ->columns([
                TextColumn::make('tanggal_report')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('marketing.nama')
                    ->label('Marketing')
                    ->searchable(),
                TextColumn::make('leads.nama_calon')
                    ->label('Konsumen')
                    ->placeholder('-'),
                TextColumn::make('jenis_aktivitas')
                    ->label('Aktivitas')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('deskripsi_progres')
                    ->label('Progres')
                    ->wrap()
                    ->limit(120)
                    ->searchable(),
            ])
            ->defaultSort('tanggal_report', 'desc')
            ->filters([
                SelectFilter::make('id_marketing')
                    ->label('Marketing')
                    ->relationship('marketing', 'nama', fn ($query) => $query->where('role', 'marketing')),
                SelectFilter::make('jenis_aktivitas')
                    ->label('Aktivitas')
                    ->options(ReportActivity::class),
                Filter::make('tanggal_report')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal_report', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal_report', '<=', $date))),
            ]);
    }
}

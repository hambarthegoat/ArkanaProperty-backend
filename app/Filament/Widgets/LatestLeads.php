<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadsResource;
use App\Models\Leads;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLeads extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Leads Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(Leads::query()->with(['perumahan', 'marketing'])->latest()->limit(5))
            ->paginated(false)
            ->recordUrl(fn (Leads $record) => LeadsResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('nama_calon')->label('Nama'),
                TextColumn::make('no_telp')->label('No. WhatsApp'),
                TextColumn::make('perumahan.nama_perumahan')->label('Perumahan'),
                TextColumn::make('status_keseriusan')->label('Status')->badge(),
                TextColumn::make('marketing.nama')->label('Marketing')->placeholder('-'),
                TextColumn::make('created_at')->label('Masuk')->since(),
            ]);
    }
}

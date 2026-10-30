<?php

namespace App\Filament\Resources\Perumahans\RelationManagers;

use App\Models\Blok;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class BlokRelationManager extends RelationManager
{
    protected static string $relationship = 'blok';

    protected static ?string $title = 'Blok';

    protected static ?string $modelLabel = 'blok';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Blok')
                    ->placeholder('Blok A')
                    ->required()
                    ->maxLength(50)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('id_perumahan', $this->getOwnerRecord()->getKey()),
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->modifyQueryUsing(fn ($query) => $query->withJumlahRumah())
            ->columns([
                TextColumn::make('nama')
                    ->label('Blok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jumlah_rumah')
                    ->label('Jumlah Rumah'),
            ])
            ->defaultSort('nama')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Blok $record) {
                        if ($jumlah = $record->houses()->count()) {
                            Notification::make()
                                ->title('Blok tidak bisa dihapus')
                                ->body("{$record->nama} masih berisi {$jumlah} rumah.")
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ]);
    }
}

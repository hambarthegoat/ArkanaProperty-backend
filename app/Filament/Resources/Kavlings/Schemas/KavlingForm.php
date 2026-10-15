<?php

namespace App\Filament\Resources\Kavlings\Schemas;

use App\Enums\KavlingStatus;
use App\Models\TipeRumah;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class KavlingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('id_tipe')
                    ->label('Perumahan / Tipe Rumah')
                    ->options(fn () => TipeRumah::with('perumahan')->get()
                        ->mapWithKeys(fn (TipeRumah $tipe) => [$tipe->id => $tipe->perumahan->nama_perumahan.' — '.$tipe->nama_tipe]))
                    ->searchable()
                    ->required(),
                TextInput::make('blok')
                    ->placeholder('Blok A')
                    ->maxLength(50),
                TextInput::make('nomor_kavling')
                    ->label('Nomor Kavling')
                    ->placeholder('A-01')
                    ->required()
                    ->maxLength(50),
                Select::make('status')
                    ->options(KavlingStatus::class)
                    ->default(KavlingStatus::Tersedia)
                    ->live()
                    ->required(),
                Select::make('id_leads')
                    ->label('Pembeli (Lead)')
                    ->relationship('leads', 'nama_calon')
                    ->searchable()
                    ->preload()
                    ->hidden(fn (Get $get) => in_array($get('status'), [null, KavlingStatus::Tersedia, KavlingStatus::Tersedia->value], true))
                    ->helperText('Kosongkan bila belum ada pembeli. Status "Tersedia" otomatis melepas pembeli.'),
            ]);
    }
}

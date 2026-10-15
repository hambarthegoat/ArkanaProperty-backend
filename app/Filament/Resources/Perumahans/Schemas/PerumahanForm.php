<?php

namespace App\Filament\Resources\Perumahans\Schemas;

use App\Enums\PerumahanStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PerumahanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_perumahan')
                    ->label('Nama Perumahan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('lokasi')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Kec. Mayangan, Probolinggo'),
                Select::make('status')
                    ->options(PerumahanStatus::class)
                    ->default(PerumahanStatus::Aktif)
                    ->required(),
                FileUpload::make('gambar')
                    ->image()
                    ->disk('public')
                    ->directory('perumahan')
                    ->maxSize(5120),
                Textarea::make('deskripsi')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}

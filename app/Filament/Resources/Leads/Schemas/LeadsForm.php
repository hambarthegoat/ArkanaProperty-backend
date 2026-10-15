<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Calon Pembeli')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_calon')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('no_telp')
                            ->label('No. WhatsApp')
                            ->tel()
                            ->required(),
                        Select::make('id_perumahan')
                            ->label('Perumahan')
                            ->relationship('perumahan', 'nama_perumahan')
                            ->required(),
                        Select::make('id_tipe')
                            ->label('Tipe Diminati')
                            ->relationship('tipeRumah', 'nama_tipe'),
                        Textarea::make('pesan')
                            ->disabled()
                            ->columnSpanFull(),
                        FileUpload::make('file_ktp')
                            ->label('KTP')
                            ->disk('local')
                            ->directory('ktp')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120)
                            ->helperText('Wajib ada sebelum lead bisa divalidasi.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Penanganan')
                    ->columns(2)
                    ->schema([
                        Select::make('status_keseriusan')
                            ->label('Status')
                            ->options(LeadStatus::class)
                            ->disabled()
                            ->helperText('Gunakan aksi Validasi / Tolak untuk mengubah status.'),
                        Select::make('id_marketing')
                            ->label('Marketing')
                            ->relationship('marketing', 'nama', fn ($query) => $query->where('role', 'marketing'))
                            ->disabled(fn ($record) => $record?->status_keseriusan !== LeadStatus::Validated),
                        Select::make('prioritas')
                            ->options(LeadPriority::class),
                        Textarea::make('catatan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

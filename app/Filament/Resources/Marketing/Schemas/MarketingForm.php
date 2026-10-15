<?php

namespace App\Filament\Resources\Marketing\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MarketingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('no_telp')
                    ->label('No. WhatsApp')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(6)
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Kosongkan saat edit bila tidak ingin mengganti password.'),
            ]);
    }
}

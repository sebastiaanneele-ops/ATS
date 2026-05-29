<?php

namespace App\Filament\Resources\PipelineStages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PipelineStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Naam')
                    ->required()
                    ->maxLength(255),
                Select::make('color')
                    ->label('Kleur')
                    ->options([
                        'gray' => 'Grijs',
                        'info' => 'Blauw',
                        'primary' => 'Primair',
                        'success' => 'Groen',
                        'warning' => 'Oranje',
                        'danger' => 'Rood',
                    ])
                    ->default('gray')
                    ->native(false)
                    ->required(),
                TextInput::make('position')
                    ->label('Volgorde')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_default')
                    ->label('Standaardfase voor nieuwe sollicitaties'),
            ]);
    }
}

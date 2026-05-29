<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Behandeling')
                    ->description('Pas de fase en status van deze sollicitatie aan.')
                    ->columns(2)
                    ->schema([
                        Select::make('pipeline_stage_id')
                            ->label('Fase')
                            ->relationship('stage', 'name')
                            ->native(false)
                            ->preload(),
                        Select::make('status')
                            ->label('Status')
                            ->options(ApplicationStatus::class)
                            ->default(ApplicationStatus::New->value)
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Gegevens sollicitant')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Naam')->disabled(),
                        TextInput::make('email')->label('E-mailadres')->disabled(),
                        TextInput::make('phone')->label('Telefoon')->disabled(),
                        TextInput::make('vacancy.title')->label('Vacature')->disabled(),
                        Textarea::make('motivation')
                            ->label('Motivatie')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(6),
                    ]),
            ]);
    }
}

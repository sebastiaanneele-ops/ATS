<?php

namespace App\Filament\Resources\PipelineStages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PipelineStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fase')
                    ->columns(2)
                    ->schema([
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
                    ]),

                Section::make('E-mail naar kandidaat')
                    ->description('Verstuur automatisch een e-mail wanneer een sollicitatie in deze fase komt. Gebruik {{naam}}, {{vacature}} en {{fase}} als placeholders.')
                    ->schema([
                        Toggle::make('notify_applicant')
                            ->label('Stuur automatisch een e-mail bij binnenkomst in deze fase'),
                        TextInput::make('email_subject')
                            ->label('Onderwerp')
                            ->maxLength(255),
                        RichEditor::make('email_body')
                            ->label('Bericht')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

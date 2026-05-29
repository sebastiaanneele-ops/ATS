<?php

namespace App\Filament\Resources\Vacancies\Schemas;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vacature')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Functietitel')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('URL-slug')
                            ->helperText('Laat leeg om automatisch te genereren uit de titel.')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('department')->label('Afdeling'),
                        TextInput::make('location')->label('Locatie'),
                        Select::make('employment_type')
                            ->label('Dienstverband')
                            ->options(EmploymentType::class),
                        TextInput::make('hours')
                            ->label('Uren')
                            ->placeholder('bijv. 32-40 uur'),
                    ]),

                Section::make('Inhoud')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Functieomschrijving')
                            ->columnSpanFull(),
                        RichEditor::make('requirements')
                            ->label('Wat we vragen')
                            ->columnSpanFull(),
                    ]),

                Section::make('Arbeidsvoorwaarden')
                    ->columns(2)
                    ->schema([
                        TextInput::make('salary_min')
                            ->label('Salaris vanaf')
                            ->numeric()
                            ->prefix('€')
                            ->suffix('per maand'),
                        TextInput::make('salary_max')
                            ->label('Salaris tot')
                            ->numeric()
                            ->prefix('€')
                            ->suffix('per maand'),
                    ]),

                Section::make('Publicatie')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(VacancyStatus::class)
                            ->default(VacancyStatus::Draft->value)
                            ->required(),
                        TextInput::make('apply_email')
                            ->label('Sollicitatie-e-mail')
                            ->email(),
                        DateTimePicker::make('published_at')
                            ->label('Publicatiedatum')
                            ->helperText('Vanaf dit moment is de vacature online zichtbaar.'),
                        DatePicker::make('closes_at')
                            ->label('Sluitingsdatum'),
                    ]),
            ]);
    }
}

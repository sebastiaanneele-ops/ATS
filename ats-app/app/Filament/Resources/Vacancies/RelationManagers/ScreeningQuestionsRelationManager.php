<?php

namespace App\Filament\Resources\Vacancies\RelationManagers;

use App\Models\ScreeningQuestion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScreeningQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'screeningQuestions';

    protected static ?string $title = 'Screeningvragen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Vraag')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        ScreeningQuestion::TYPE_BOOLEAN => 'Ja / Nee',
                        ScreeningQuestion::TYPE_CHOICE => 'Meerkeuze',
                        ScreeningQuestion::TYPE_TEXT => 'Open tekst',
                    ])
                    ->default(ScreeningQuestion::TYPE_BOOLEAN)
                    ->required()
                    ->native(false)
                    ->live(),
                Toggle::make('is_required')
                    ->label('Verplicht')
                    ->default(true),
                TagsInput::make('options')
                    ->label('Keuzes')
                    ->helperText('Voeg de antwoordopties toe (Enter na elke optie).')
                    ->visible(fn (Get $get) => $get('type') === ScreeningQuestion::TYPE_CHOICE)
                    ->live()
                    ->columnSpanFull(),
                Select::make('knockout_values')
                    ->label('Knock-out bij dit antwoord')
                    ->helperText('Geeft de kandidaat dit antwoord, dan wordt de sollicitatie automatisch afgewezen.')
                    ->multiple()
                    ->native(false)
                    ->options(function (Get $get): array {
                        return match ($get('type')) {
                            ScreeningQuestion::TYPE_BOOLEAN => ['Ja' => 'Ja', 'Nee' => 'Nee'],
                            ScreeningQuestion::TYPE_CHOICE => collect($get('options') ?? [])
                                ->mapWithKeys(fn ($o) => [$o => $o])
                                ->all(),
                            default => [],
                        };
                    })
                    ->visible(fn (Get $get) => in_array($get('type'), [
                        ScreeningQuestion::TYPE_BOOLEAN,
                        ScreeningQuestion::TYPE_CHOICE,
                    ], true))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('position')
                    ->label('#'),
                TextColumn::make('label')
                    ->label('Vraag')
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        ScreeningQuestion::TYPE_BOOLEAN => 'Ja / Nee',
                        ScreeningQuestion::TYPE_CHOICE => 'Meerkeuze',
                        default => 'Open tekst',
                    }),
                TextColumn::make('knockout_values')
                    ->label('Knock-out bij')
                    ->badge()
                    ->color('danger')
                    ->placeholder('—'),
                IconColumn::make('is_required')
                    ->label('Verplicht')
                    ->boolean(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->headerActions([
                CreateAction::make()->label('Vraag toevoegen'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

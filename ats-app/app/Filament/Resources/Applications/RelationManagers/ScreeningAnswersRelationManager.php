<?php

namespace App\Filament\Resources\Applications\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScreeningAnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'screeningAnswers';

    protected static ?string $title = 'Screeningantwoorden';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_label')
            ->columns([
                TextColumn::make('question_label')
                    ->label('Vraag')
                    ->wrap(),
                TextColumn::make('answer')
                    ->label('Antwoord')
                    ->placeholder('—'),
                IconColumn::make('is_knockout')
                    ->label('Knock-out')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

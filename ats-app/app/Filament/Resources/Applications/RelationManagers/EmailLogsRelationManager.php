<?php

namespace App\Filament\Resources\Applications\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'emailLogs';

    protected static ?string $title = 'Verzonden e-mails';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('to')->label('Aan')->disabled(),
                TextInput::make('subject')->label('Onderwerp')->disabled()->columnSpanFull(),
                Textarea::make('body')->label('Bericht')->disabled()->rows(10)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('subject')
                    ->label('Onderwerp')
                    ->wrap(),
                TextColumn::make('to')
                    ->label('Aan'),
                TextColumn::make('sent_at')
                    ->label('Verzonden')
                    ->dateTime('d-m-Y H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

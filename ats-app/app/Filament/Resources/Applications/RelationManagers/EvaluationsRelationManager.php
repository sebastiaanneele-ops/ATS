<?php

namespace App\Filament\Resources\Applications\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    protected static ?string $title = 'Beoordelingen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('score')
                    ->label('Score')
                    ->options([
                        1 => '1 – Onvoldoende',
                        2 => '2 – Matig',
                        3 => '3 – Voldoende',
                        4 => '4 – Goed',
                        5 => '5 – Uitstekend',
                    ])
                    ->required()
                    ->native(false),
                Textarea::make('comment')
                    ->label('Toelichting')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('score')
            ->columns([
                TextColumn::make('score')
                    ->label('Score')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state.'/5')
                    ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),
                TextColumn::make('comment')
                    ->label('Toelichting')
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('user.name')
                    ->label('Door')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Op')
                    ->dateTime('d-m-Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Beoordeling toevoegen'),
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

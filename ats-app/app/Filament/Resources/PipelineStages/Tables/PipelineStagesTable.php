<?php

namespace App\Filament\Resources\PipelineStages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PipelineStagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Naam')
                    ->badge()
                    ->color(fn ($record) => $record->color ?? 'gray')
                    ->searchable(),
                TextColumn::make('applications_count')
                    ->label('Sollicitaties')
                    ->counts('applications')
                    ->badge(),
                IconColumn::make('is_default')
                    ->label('Standaard')
                    ->boolean(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

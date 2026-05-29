<?php

namespace App\Filament\Resources\Vacancies\Tables;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Functietitel')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->department),
                TextColumn::make('location')
                    ->label('Locatie')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('employment_type')
                    ->label('Dienstverband')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Gepubliceerd')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('closes_at')
                    ->label('Sluit op')
                    ->date('d-m-Y')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(VacancyStatus::class),
                SelectFilter::make('employment_type')
                    ->label('Dienstverband')
                    ->options(EmploymentType::class),
            ])
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

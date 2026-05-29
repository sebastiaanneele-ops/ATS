<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Sollicitant')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),
                TextColumn::make('vacancy.title')
                    ->label('Vacature')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('stage.name')
                    ->label('Fase')
                    ->badge()
                    ->color(fn ($record) => $record->stage?->color ?? 'gray')
                    ->placeholder('—'),
                TextColumn::make('average_score')
                    ->label('Score')
                    ->state(fn ($record) => $record->averageScore() !== null ? $record->averageScore().'/5' : '—'),
                IconColumn::make('cv_path')
                    ->label('CV')
                    ->boolean()
                    ->state(fn ($record) => $record->hasCv()),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Ontvangen')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('pipeline_stage_id')
                    ->label('Fase')
                    ->relationship('stage', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ApplicationStatus::class),
                SelectFilter::make('vacancy')
                    ->label('Vacature')
                    ->relationship('vacancy', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('cv')
                    ->label('CV')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn ($record) => route('admin.applications.cv', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->hasCv()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\RelationManagers\EvaluationsRelationManager;
use App\Filament\Resources\Applications\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|\UnitEnum|null $navigationGroup = 'Werving';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Sollicitaties';

    protected static ?string $modelLabel = 'sollicitatie';

    protected static ?string $pluralModelLabel = 'sollicitaties';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
            EvaluationsRelationManager::class,
        ];
    }

    /**
     * Sollicitaties worden via de website aangemaakt, niet handmatig in het panel.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $new = static::getModel()::query()
            ->where('status', ApplicationStatus::New->value)
            ->count();

        return $new > 0 ? (string) $new : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}

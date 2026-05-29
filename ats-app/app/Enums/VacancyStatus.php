<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum VacancyStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Concept',
            self::Published => 'Gepubliceerd',
            self::Closed => 'Gesloten',
            self::Archived => 'Gearchiveerd',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Closed => 'warning',
            self::Archived => 'danger',
        };
    }
}

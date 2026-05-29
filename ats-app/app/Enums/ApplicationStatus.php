<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InReview = 'in_review';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Nieuw',
            self::InReview => 'In behandeling',
            self::Rejected => 'Afgewezen',
            self::Hired => 'Aangenomen',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'info',
            self::InReview => 'warning',
            self::Rejected => 'danger',
            self::Hired => 'success',
        };
    }
}

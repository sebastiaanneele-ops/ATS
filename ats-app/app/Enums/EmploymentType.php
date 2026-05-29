<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmploymentType: string implements HasLabel
{
    case FullTime = 'fulltime';
    case PartTime = 'parttime';
    case Internship = 'stage';
    case Temporary = 'tijdelijk';
    case Freelance = 'freelance';

    public function getLabel(): string
    {
        return match ($this) {
            self::FullTime => 'Fulltime',
            self::PartTime => 'Parttime',
            self::Internship => 'Stage',
            self::Temporary => 'Tijdelijk',
            self::Freelance => 'Freelance',
        };
    }
}

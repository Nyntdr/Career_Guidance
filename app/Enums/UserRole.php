<?php

namespace App\Enums;

enum UserRole: string
{
    case Candidate = 'candidate';
    case Counselor = 'counselor';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Candidate => 'Candidate',
            self::Counselor => 'Career Counselor',
            self::Admin => 'Administrator',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Candidate => 'dashboard',
            self::Counselor => 'counselor.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}

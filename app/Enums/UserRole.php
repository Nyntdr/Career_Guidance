<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Admin => 'Administrator',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::User => 'dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}

<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
    case Operator = 'operator';

    /**
     * Human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuario',
            self::Admin => 'Administrador',
            self::Operator => 'Operador',
        };
    }
}

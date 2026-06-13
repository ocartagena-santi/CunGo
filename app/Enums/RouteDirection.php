<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum RouteDirection: string
{
    case Ida = 'ida';
    case Vuelta = 'vuelta';

    /**
     * Human-readable label for the direction.
     */
    public function label(): string
    {
        return match ($this) {
            self::Ida => 'Ida',
            self::Vuelta => 'Vuelta',
        };
    }
}

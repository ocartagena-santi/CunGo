<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum VehicleType: string
{
    case Urban = 'urban';
    case Camion = 'camion';
    case Combi = 'combi';

    /**
     * Human-readable label for the vehicle type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Urban => 'Urban',
            self::Camion => 'Camión',
            self::Combi => 'Combi',
        };
    }
}

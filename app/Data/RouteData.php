<?php

namespace App\Data;

use App\Enums\VehicleType;
use App\Models\Route;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class RouteData extends Data
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $color,
        public VehicleType $vehicleType,
        public string $fare,
        public ?int $frequencyMinutes,
        public ?string $operatingHours,
        public ?string $description,
        public bool $isActive,
    ) {
    }

    public static function fromModel(Route $route): self
    {
        return new self(
            id: $route->id,
            code: $route->code,
            name: $route->name,
            color: $route->color,
            vehicleType: $route->vehicle_type,
            fare: $route->fare,
            frequencyMinutes: $route->frequency_minutes,
            operatingHours: $route->operating_hours,
            description: $route->description,
            isActive: $route->is_active,
        );
    }
}

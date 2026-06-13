<?php

namespace App\Data;

use App\Models\Stop;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class StopData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public float $lat,
        public float $lng,
        public ?string $landmark,
        public ?string $colonia,
        public bool $isLandmark,
    ) {
    }

    public static function fromModel(Stop $stop): self
    {
        return new self(
            id: $stop->id,
            name: $stop->name,
            lat: $stop->location->latitude,
            lng: $stop->location->longitude,
            landmark: $stop->landmark,
            colonia: $stop->colonia,
            isLandmark: $stop->is_landmark,
        );
    }
}

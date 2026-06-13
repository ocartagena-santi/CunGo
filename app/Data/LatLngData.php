<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class LatLngData extends Data
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }
}

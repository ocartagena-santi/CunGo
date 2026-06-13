<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $sequence
 * @property string $direction
 */
class RouteStop extends Pivot
{
    protected $table = 'route_stop';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
        ];
    }
}

<?php

namespace Esanj\AppService\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $service_id
 * @property string $key
 * @property mixed $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ServiceMeta extends Model
{
    protected $fillable = [
        'service_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

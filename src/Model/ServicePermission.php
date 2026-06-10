<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $key
 * @property string|null $display_name
 * @property string|null $description
 */
class ServicePermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'display_name',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'service_permission_map',
            'permission_id',
            'service_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public static function findByKey(string $key): ?self
    {
        return static::query()->where('key', $key)->first();
    }
}
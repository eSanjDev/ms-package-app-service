<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $client_id
 * @property bool $is_active
 * @property string|null $extra
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'client_id',
        'is_active',
        'extra',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'extra' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            ServicePermission::class,
            'service_permission_map',
            'service_id',
            'permission_id'
        );
    }

    public function meta(): HasMany
    {
        return $this->hasMany(ServiceMeta::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Methods
    |--------------------------------------------------------------------------
    */

    public function hasPermission(string $permissionKey): bool
    {
        return $this->permissions()->where('key', $permissionKey)->exists();
    }

    public function grantPermission(ServicePermission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function revokePermission(ServicePermission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Meta Methods
    |--------------------------------------------------------------------------
    */

    public function getMeta(string $key, $default = null)
    {
        $meta = $this->meta()->where('key', $key)->first();

        return $meta?->value ?? $default;
    }

    public function setMeta(string $key, $value): ServiceMeta
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function deleteMeta(string $key): bool
    {
        return $this->meta()->where('key', $key)->delete() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByClientId($query, string $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}

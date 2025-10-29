<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'client_id',
        'is_active',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(ServicePermission::class, 'service_permissions_map', 'service_id', 'permission_id');
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('key', $permission)->exists();
    }

    public function getMeta($key)
    {
        return $this->meta->where('key', $key)->value('value');
    }

    public function meta()
    {
        return $this->hasMany(ServiceMeta::class);
    }

    public function setMeta(string $key, $value)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}

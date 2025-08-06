<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'client_id',
        'is_active',
    ];

    public function getMeta($key)
    {
        return $this->meta->where('key', $key)->value('value');
    }

    public function meta(): HasMany
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

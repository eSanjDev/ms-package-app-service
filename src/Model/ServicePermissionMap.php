<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;

class ServicePermissionMap extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'permission_id',
    ];
}

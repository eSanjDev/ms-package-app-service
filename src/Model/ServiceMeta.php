<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;

class ServiceMeta extends Model
{
    protected $fillable = [
        'service_id',
        'key',
        'value',
    ];
}

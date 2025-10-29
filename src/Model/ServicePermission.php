<?php

namespace Esanj\AppService\Model;

use Illuminate\Database\Eloquent\Model;

class ServicePermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'display_name',
        'description',
    ];
}

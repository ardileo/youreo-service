<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileConfig extends Model
{
    protected $table = "mobile_config";

    protected $guarded = [];

    protected $hidden = [
        'id'
    ];
}

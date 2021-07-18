<?php

namespace App\Models;

use App\Helpers\Galileyo;
use Illuminate\Database\Eloquent\Model;

class EventRequireActivity extends Model
{
    protected $table = "event_require_activities";

    protected $guarded = [];

    public function eventRequire()
    {
        return $this->belongsTo(EventRequire::class,'id_require');
    }

    public function idPublic()
    {
        return Galileyo::id_encrypt($this->id);
    }
}

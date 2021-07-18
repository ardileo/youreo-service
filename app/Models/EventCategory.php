<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    protected $table = "event_category";

    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

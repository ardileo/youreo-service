<?php

namespace App\Models;

use App\Helpers\Galileyo;
use Illuminate\Database\Eloquent\Model;

class EventInvitation extends Model
{
    protected $table = "event_invitations";

    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function idPublic()
    {
        return Galileyo::id_encrypt($this->id);
    }
}

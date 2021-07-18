<?php

namespace App\Models;

use App\Helpers\Galileyo;
use Illuminate\Database\Eloquent\Model;

class UserNotif extends Model
{
    protected $table = "user_notifications";

    protected $guarded = [];

    protected $casts = [
        'delete_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function idPublic()
    {
        return Galileyo::id_encrypt($this->id);
    }

}

<?php

namespace App\Models;

use App\Helpers\Galileyo;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $primaryKey = 'id_user';

    protected $guarded = ["",];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token',
    ];

    public function idPublic()
    {
        return Galileyo::id_encrypt($this->id_user);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'owner');
    }

    public function notifs()
    {
        return $this->hasMany(UserNotif::class, 'user_id');
    }

    public function getImageProfile()
    {
        $images = ["https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500", "https://images.pexels.com/photos/1264210/pexels-photo-1264210.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500", "https://images.pexels.com/photos/428364/pexels-photo-428364.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500"];
        return $images[rand(0, count($images) - 1)];
    }

    public function getDisplayName()
    {
       return $this->full_name;
    }
}

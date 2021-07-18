<?php

namespace App\Models;

use App\Helpers\Galileyo;
use App\Http\Resources\EventRequireResource;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'members' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner');
    }

    public function idPublic()
    {
        return Galileyo::id_encrypt($this->id);
    }

    public function required()
    {
        return $this->hasMany(EventRequire::class, 'id_event');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category');
    }

    public function activities()
    {
        return $this->required()->with('activities');
    }

    public function incomes()
    {
        return $this->required()->with('incomes');
    }

    public function invitations()
    {
        return $this->hasMany(EventInvitation::class, 'id_event');
    }

    public function hasMember($user)
    {
        $sa = isset($this->members) ? $this->members : [];
        foreach ($sa as $value) {
            if ($value['id'] == $user->id_user) {
                return true;
                break;
            }
        }
        return false;
    }

    public function contributors()
    {
        $sa = isset($this->members) ? $this->members : [];
        $result = collect();
        foreach ($sa as $value) {
            if (isset($value['id'])) {
                $member = User::where('id_user', $value['id'])->first();
                $member = UserResource::make($member)->toArray(request());
                $member['added_by'] = User::find($value['added_by'])->getDisplayName();
                $member['added_at'] = $value['added_at'];
                $member['confirm_at'] = $value['confirm_at'];

                $data_for = isset(request()->user_for) ? request()->user_for : '';
                if ($data_for == 'detail') {
                    $reqManaged = [];
                    foreach ($this->required()->whereJsonContains('req_contribs', [['id' => $value['id']]])->get(['id', 'req_name']) as $k => $value) {
                        $reqManaged[$k]['id'] = Galileyo::id_encrypt($value['id']);
                        $reqManaged[$k]['req_name'] = $value['req_name'];
                    }
                    $member['mange_role'] = isset($value['manage_role']) ? $value['manage_role'] : ($member == $this->user() ? 'OWNER' : null);
                    $member['mange_req'] = $reqManaged;
                }
                $result->add($member);
            }
        }
        return $result;
    }

    public function getDisplayImage()
    {
        return "https://assets.simpleviewinc.com/simpleview/image/fetch/c_limit,q_75,w_1200/https://assets.simpleviewinc.com/simpleview/image/upload/crm/sanmateoca/shutterstock_61104537504_8ef67d97-5056-a36a-0b9f5fc892eae781.jpg";
    }
}

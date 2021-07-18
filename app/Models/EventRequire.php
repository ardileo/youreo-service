<?php

namespace App\Models;

use App\Helpers\Galileyo;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Model;

class EventRequire extends Model
{
    protected $table = "event_require";

    protected $guarded = [];

    protected $casts = [
        'req_contribs' => 'array',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'logs' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    public function idPublic()
    {
        return Galileyo::id_encrypt($this->id);
    }

    public function activities()
    {
        return $this->hasMany(EventRequireActivity::class, 'id_require');
    }

    public function incomes()
    {
        return $this->hasMany(EventRequireIncome::class, 'id_require');
    }

    public function invitations()
    {
        return $this->hasMany(EventRequireInvitation::class, 'id_require');
    }

    public function contributors()
    {
        $result = [];
        if (isset($this->req_contribs) && is_array($this->req_contribs)) {
            $sa = $this->req_contribs;
            request()->request->add(['get_data' => ['contact']]);
            foreach ($sa as $key => $value) {
                if (isset($value['id'])) {
                    $member = User::where('id_user', $value['id'])->first();
                    $result[$key] = UserResource::make($member)->toArray(request());
                    $result[$key]['added_by'] = User::find($value['added_by'])->getDisplayName();
                    $result[$key]['added_at'] = $value['added_at'];
                    $result[$key]['roles'] = ["gatau"];
                }
            }
        }

        return $result;
    }
}

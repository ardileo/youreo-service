<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Models\EventRequire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotifResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        $title = $this->title;
        $body = $this->body;
        $data = null;

        if ($this->data) {
            $data = json_decode($this->data);
            if ($data->type == "confirm_invitation") {
                $adder = User::find($data->added_by);
                $evenRequire = EventRequire::find($data->require_id);
                $event = Event::find($data->event_id);
                $confirmed = isset($data->confirmed) ? $data->confirmed : null;
                $data = [
                    "invitation" => [
                        "added_by" => $adder->full_name,
                        "event" => [
                            "id" => $event->idPublic(),
                            "title" => $event->name
                        ],
                        "image" => $event->getDisplayImage(),
                        "require" => !$evenRequire ? null : [
                            "id" => $evenRequire->idPublic(),
                            "req_name" => $evenRequire->req_name
                        ],
                        "confirmed" => $confirmed,
                    ]
                ];
                $title = $event->name;
            }
        }

        $main = [
            'id' => $this->idPublic(),
            'title' => $title,
            'body' => $body,
            'data' => $data,

            // 'title' => $this->name,
            // 'guests' => $this->guests,
            // 'budget_limit' => $this->budgets,
            // 'date_start' => $this->date_start->timestamp,
            // 'date_end' => $this->date_end->timestamp,
            // 'owner_id' => $this->user()->first()->idPublic(),
            // // 'images' => $this->images,
            // 'image' => "https://assets.simpleviewinc.com/simpleview/image/fetch/c_limit,q_75,w_1200/https://assets.simpleviewinc.com/simpleview/image/upload/crm/sanmateoca/shutterstock_61104537504_8ef67d97-5056-a36a-0b9f5fc892eae781.jpg",
            // 'requires' => EventRequireResource::collection($requirement),
        ];

        return $main;
    }
}

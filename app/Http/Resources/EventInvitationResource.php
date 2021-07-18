<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventInvitationResource extends JsonResource
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
        return [
            'id' => $this->idPublic(),
            'invit_name' => $this->invit_name,
            'invit_phone' => $this->invit_phone,
            'invit_email' => $this->invit_email,
            'added_by' => $this->added_by,
            'status' => $this->status,
            'created_at' => $this->created_at->timestamp
        ];
    }
}

<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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

        $requirement = $this->required->where('deleted_at', '=', null);
        $invit_now = $this->invitations->count();
        $invit_limit = $requirement->where('req_name', 'invitation_tracking')->first();
        $invit_limit = $invit_limit ? $invit_limit->req_budget : 0;;

        $result =  collect([
            'id' => $this->idPublic(),
        ]);

        $data_for = isset($request->ev_for) ? $request->ev_for : '';
        $get_data = [];
        switch ($data_for) {
            case 'lists':
                $get_data = ['intro', 'owner', 'progress', 'date'];
                break;
            case 'dashboard':
                $get_data = ['intro', 'owner', 'budget', 'progress', 'requires', 'contribs', 'date'];
                break;
        }

        foreach ($get_data as $val) {
            if ($val == 'intro') {
                $result->put('title', $this->name);
                $result->put('category', $this->category()->first()->slug);
                $result->put('display_image', $this->getDisplayImage());
            }

            if ($val == 'owner') {
                $result->put('owner_id', $this->user()->first()->idPublic());
            }

            if ($val == 'budget') {
                $result->put('guests', $this->guests);
                $result->put('budget_limit', $this->budgets);
                $result['budget_now'] = 90000;
                $result['invitation_now'] = $invit_now;
                $result['invitation_limit'] = $invit_limit;
            }

            if ($val == 'progress') {
                $total = count($requirement);
                $remain = $requirement->where('finished', 'no')->where('status', 'on')->count();
                $completed = count($requirement->where('finished', 'yes'));
                $result['progress'] = $completed == 0 ? 0 : ceil($completed / $total * 100);
                $result['remain_tasks'] = $remain;
                $result['is_finished'] = $this->finished == 'yes';
            }

            if ($val == 'requires') {
                $result['requires'] = EventRequireResource::collection($requirement);
            }

            if ($val == 'contribs') {
                $result['contribs'] = $this->contributors();
            }

            if ($val == 'date') {
                $result->put('date_start', $this->date_start->timestamp);
                $result->put('date_end', $this->date_end->timestamp);
            }
        }
        return $result;
    }
}

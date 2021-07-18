<?php

namespace App\Http\Resources;

use App\Helpers\Galileyo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class EventRequireResource extends JsonResource
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

        if ($this->logs) {
            $finish = $this->logs['finished'];
            $finish['by'] = User::find($finish['by'])->full_name;
        }
        $finish['is'] = $this->finished == 'yes' ? 1 : 0;

        $result =  collect([
            'id' => $this->idPublic(),
            'delllll' => $this->deleted_at,
        ]);

        $data_for = isset($request->ereq_for) ? $request->ereq_for : 'detail';
        $get_data = [];
        switch ($data_for) {
            case 'lists':
                $get_data = ['intro', 'progress', 'date', 'finish'];
                break;
            case 'detail':
                $get_data = ['intro', 'budget', 'contribs', 'date', 'finish'];
                break;
        }

        foreach ($get_data as $val) {
            if ($val == 'intro') {
                $result->put('req_name', $this->req_name);
                $result->put('req_desc', $this->req_desc);
                $result->put('req_status', $this->status == 'on' ? 1 : 0);
            }
            if ($val == 'budget') {
                $result->put('req_budget', $this->req_budget);
            }
            if ($val == 'contribs') {
                $result->put('req_contribs', $this->contributors());
            }
            if ($val == 'date') {
                $result->put('date_start',  $this->date_start ? $this->date_start->timestamp : null);
                $result->put('date_end', $this->date_end ? $this->date_end->timestamp : null);
            }
            if ($val == 'finish') {
                $result->put('finished', $finish['is'] ? $finish['is'] : null);
                $result->put('finished_at', isset($finish['at']) ? $finish['at'] : null);
                $result->put('finished_by', isset($finish['by']) ? $finish['by'] : null);
            }
        };
        return $result;
    }
}

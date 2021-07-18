<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->wrap("user");
        $me = auth()->user();

        $result =  collect([
            'id' => $this->idPublic(),
        ]);

        // Cara gunain tambahkan ini :
        // $request->request->add(['user_for'=>'detail']);


        $data_for = isset($request->user_for) ? $request->user_for : '';
        $get_data = [];
        switch ($data_for) {
            case 'lists':
                $get_data = ['bioless'];
                break;
            case 'detail':
                $get_data = ['contact', 'bioless'];
                break;
            case 'all':
                $get_data = ['intro', 'contact', 'timestamp', 'biodata'];
                break;
            case 'credential':
                $get_data = ['intro', 'auth', 'contact', 'biodata'];
                break;
        }

        foreach ($get_data as $val) {
            if ($val == 'token') {
                $result->put('api_token', $this->api_token);
            }

            if ($val == 'pass') {
                $result->put('password', $this->password);
            }

            if ($val == 'auth') {
                $result->put('username', $this->username);
                $result->put('api_token', $this->api_token);
            }

            if ($val == 'contact') {
                $result->put('username', $this->username);
                $result->put('display_name', $this->getDisplayName());
                $result->put('email', $this->email);
                $result->put('phone', $this->phone);
            }

            if ($val == "timestamp") {
                $result->put('join_at', $this->created_at->timestamp);
                $result->put('last_auth', $this->updated_at->timestamp);
            }

            if ($val == 'biodata') {
                $result->put('image_profile', $this->getImageProfile());
                $result->put('display_name', $this->getDisplayName());
                $result->put('full_name', $this->full_name);
                $result->put('gender', $this->gender);
                $result->put('born_at', $this->born_at);
                $result->put('address', $this->address);
                $result->put('city', $this->city);
            }

            if ($val == 'bioless') {
                $result->put('image_profile', $this->getImageProfile());
                $result->put('display_name', $this->getDisplayName());
                $result->put('gender', $this->gender);
            }
        };

        // if (isset($request->its_me) && $request->its_me && $result->get('stats')) {
        //     $result->forget('isfollower');
        //     $result->forget('isfollowing');
        //     $result->put('stats', array_merge($result->get('stats'), ['coins' => 2]));
        // }

        return $result;
    }
}

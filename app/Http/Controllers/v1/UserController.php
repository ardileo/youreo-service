<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ErrorBuilder;
use App\Helpers\Galileyo;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Event;
use App\Models\EventRequire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getAllMyEvents(Request $request)
    {
        $user = Auth::user();
        $user->events;
        return [$user->events];
    }

    public function findUser(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'data' => 'required|string|min:6',
            'type' => 'in:email,phone,id'
        ]);

        if (!$validated->fails()) {
            $user = null;
            $cred = $request->data;
            switch ($request->type) {
                case 'email':
                    $user = User::where('email', $cred)->first();
                    break;

                case 'phone':
                    $user = User::where('phone', $cred)->first();
                    break;
            }

            if($user){
                $request->request->add(['user_for' => 'lists']);
                $out['code'] = 200;
                $out['result'] = [
                    "user" => UserResource::make($user)
                ];
            }else{
                $out['code'] = 404;
                $out['message'] = "User Not Found";
            }

            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }
}

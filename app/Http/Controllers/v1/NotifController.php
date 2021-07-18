<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ErrorBuilder;
use App\Helpers\Galileyo;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserNotifResource;
use App\Http\Resources\UserResource;
use App\Models\EventRequire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotifController extends Controller
{
    public function index(Request $request)
    {

        $me = Auth::user();

        $out['code'] = 200;
        $out['result'] = [
            "notifs" => UserNotifResource::collection($me->notifs),
        ];
        return response()->json($out, $out['code']);
        throw ErrorBuilder::parse(422)->toResponse();
    }
}

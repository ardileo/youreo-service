<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ErrorBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use App\Http\Resources\UserResource;
use App\Models\MobileConfig;
use App\Models\User;
use Galileyo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AppConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api_token,skipable');
    }

    public function splash(Request $request)
    {
        $headers = [
            // "X-XSS-Protection" => "1;mode=block",
            // "X-Frame-Options" => "SAMEORIGIN",
            // "X-Content-Type-Options" => "nosniff",
            // "Cache-Control" => "no-store, no-cache, max-age=0, must-revalidate",
        ];

        $data["force_update"] = json_decode(MobileConfig::where('tag','android_app_force_update')->first()->value);
        $data["last_version"] = json_decode(MobileConfig::where('tag','android_app_last_version')->first()->value);
        $myversion = request()->header('Y-AppVersion');

        $comparator = version_compare($myversion, $data['force_update']);
        $comparator = $comparator <= 0 ? -1 : version_compare($data['last_version'], $myversion);
        $data['update'] = $comparator < 0;

        $out["code"] = 200;
        $out["result"] = ["version" => $data];

        if (!$data['update']) {
            if(request()->header("Authorization")){
                if (Auth::check()) {
                    $request->request->add(['get_data' => ["token"]]);
                    $user = UserResource::make(Auth::user());
                    $out["result"]["user"] = $user;
                }else{
                    ErrorBuilder::parse(Response::HTTP_NOT_ACCEPTABLE, "Auth rejected! maybe token session expired", [
                        "error"   => [
                            "message" => "<b>Unauthenticated.</b><br/>Please log in again",
                            "btn_1" => ["text" => "Login", "action" => env("MOBILE_APP_HOST") . "logout?act=sign"],
                        ]
                    ])->toResponse();
                }
            }
        }

        return response()->json($out, $out['code'], $headers, 1);
    }

    public function feed_menu(Request $request)
    {

        $user = Auth::user();
        $top_slider = json_decode(MobileConfig::where('tag','top_sliders')->first()->value);
        $feed_items = json_decode(MobileConfig::where('tag','feed_items')->first()->value);

        $feeds = [
            "top_slider" => $top_slider,
            "feed_items" => $feed_items
        ];

        $out["code"] = 200;
        $out["result"] = $feeds;
        return response()->json($out, $out['code']);
    }
}

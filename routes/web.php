<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Helpers\Galileyo;
use App\Http\Middleware\DevelopmentCheck;
use App\Http\Resources\EventResource;
use App\Mail\SendInvitationNotif;
use App\Mail\SendMail;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    // return Galileyo::id_decrypt("ElkMwADMwADMwADM");
    DB::setDefaultConnection('on_dev');
    $me = User::find(1);
    $userTarget = User::find(2);
    $event = Event::find(1);
    $req = $event->required()->find(3);

    return Galileyo::id_encrypt(1);

    return [
        "event_contribs" => $event->contributors(),
        "req_contribs" => $req->contributors(),
    ];
});


Route::get('/lihat/{filename}', function ($filename) {

    $img_path = storage_path('uploads/image_profile') . '/' . $filename;
    if (file_exists($img_path)) {
        $file = file_get_contents($img_path);
        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }


    return response()->json([
        'message' => "image not found",
    ]);
});

Route::post('/upload', function () {
    $request = request();
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $name = time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = storage_path('uploads/image_profile');
        $image->move($destinationPath, $name);

        return response()->json(
            [
                'data' => "image is uploaded",
                'url' => url('img/app/images/' . $name),
            ]
        );
    }
    return response()->json(['error']);
});

// http://youreoapi.test/data/cover/id?dev
// http://youreoapi.test/data/cover/id?dev?res=300
Route::get('/data/{fileDir}/{fileId}[?res={res}[?{branch}]]', [
    'middleware' => 'branch:addon',
    'uses' => 'FilesController@getFile'
]);
Route::get('/event/confirm-contrib-invitation/{data:.*}', 'v1\EventMailController@mailInvitConfirm');
Route::group(['prefix' => '/{branch_version}', 'middleware' => 'branch:version'], function () {
    Route::get('checkup', 'v1\AppConfigController@splash');
    Route::post('signin', 'v1\AuthController@signin');
    Route::post('signup', 'v1\AuthController@signup');

    Route::group(['middleware' => 'auth:api_token'], function () {
        Route::get('feed_menu', 'v1\AppConfigController@feed_menu');
        Route::post('user/find', 'v1\UserController@findUser');

        Route::group(['prefix' => '/event_manage'], function () {
            Route::post('generate', 'v1\EventController@generateFeature');
            Route::post('create', 'v1\EventController@newEvent');

            Route::get('stats', 'v1\EventController@getStats');
            Route::get('lists[/{filter}]', 'v1\EventController@getLists');

            Route::get('dashboard/{id}', 'v1\EventController@getDashboard');
            Route::get('dashboard/{id}/contributors', 'v1\EventController@getContributors');
            Route::get('require/{id}', 'v1\EventController@getRequire');
            Route::post('require/delete', 'v1\EventController@deleteRequire');
            Route::post('require/edit', 'v1\EventController@setRequire');
            Route::post('require/add', 'v1\EventController@addRequire');

            Route::post('invite', 'v1\EventController@inviteContributor');
        });

        Route::group(['prefix' => 'notif'], function () {
            Route::get('/get', 'v1\NotifController@index');
        });
    });
});

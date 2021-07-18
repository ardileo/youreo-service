<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ErrorBuilder;
use App\Helpers\FirebaseNotifMobile;
use App\Helpers\Galileyo;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventRequireResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Mail\SendInvitationNotif;
use App\Models\Event;
use App\Models\EventRequire;
use App\Models\User;
use App\Models\UserNotif;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function generateFeature(Request $request)
    {
        $slug = $request->slug;
        if ($slug) {
            $dat = DB::table('event_category')->where('slug', $slug)->first();
            if ($dat) {
                $features = json_decode($dat->pattern);
                $features->cat_id = Galileyo::id_encrypt($dat->id);

                $out['code'] = 200;
                $out['result'] = $features;
                return response()->json($out, $out['code']);
            }
        }
        throw ErrorBuilder::parse(422)->toResponse();
    }

    public function newEvent(Request $request)
    {
        $data = $request->datas;
        $data = json_decode($data, true);
        if ($data) {
            $v = Validator::make($data, [
                'event_cat_id' => 'required',
                'event_cat_slug' => 'required',
                'event_name' => 'required',
                'event_guests' => 'required|numeric|min:0|not_in:0',
                'event_budgets' => 'required|numeric|min:0|not_in:0',
                'event_date_start' => 'required|numeric|min:0|not_in:0',
                'event_features' => 'required|array',
            ]);

            if ($v->fails()) {
                throw ErrorBuilder::parse(422, "Gagal membuat event", [
                    "error"   => [
                        "message" => $v->errors()->first(),
                        "btn_1" => ["text" => "Retry", "action" => "dismiss()"]
                    ]
                ])->toResponse();
            }

            $data['event_cat_id'] = Galileyo::id_decrypt($data['event_cat_id']);
            $data['date_start'] = Carbon::createFromTimestamp($data['event_date_start']);
            $data['date_end'] = isset($data['date_end']) ? Carbon::createFromTimestamp($data['date_end']) : $data['date_start'];

            $event = new Event;

            DB::transaction(function () use ($data, $event) {
                $event->name = $data['event_name'];
                $event->category = $data['event_cat_id'];
                $event->guests = $data['event_guests'];
                $event->budgets = $data['event_budgets'];
                $event->date_start = $data['date_start'];
                $event->date_end = $data['date_end'];
                $event->owner = Auth::user()->id_user;
                $event->status = 'publish';
                $event->save();

                $count = 0;
                foreach ($data['event_features'] as $key) {
                    $req = new EventRequire;
                    $req->id_event = $event->id;
                    $req->req_name = $key['name'];
                    $req->finished = 'no';
                    $req->status =  $key['check'] == "true" ? "on" : "off";
                    $req->save();
                    $count++;
                }

                if ($count != count($data['event_features'])) {
                    DB::rollback();
                } else {
                    DB::commit();
                }
            });

            $out['code'] = 201;
            $out['result'] = [
                "event" => $event
            ];
            return response()->json($out, $out['code']);
        }

        throw ErrorBuilder::parse(422)->toResponse();
    }

    public function getStats(Request $request)
    {
        $user = Auth::user();

        // FirebaseNotifMobile::sendPushNotif();

        $request->request->add(['ev_for' => 'lists']);
        $event_owned = $user->events;
        $event_joined = Event::whereJsonContains('members', [['id' => $user->id_user]])->get();

        $out['code'] = 200;
        $out['result'] = [
            "event_stats" => [
                "total" => $event_owned->count(),
                "finished" => $event_owned->where('finished', 'yes')->count(),
                "ongoing" => count($event_owned->where('finished', '!=', 'yes')),
            ],
            "owned" => EventResource::collection($event_owned),
            "joined" => EventResource::collection($event_joined)
        ];
        return response()->json($out, $out['code']);

        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function getLists(Request $request)
    {
        $user = Auth::user();
        if ($user->events) {
            $request->request->add(['ev_for' => 'lists']);

            switch ($request->filter) {
                case 'ongoing':
                    $events = $user->events->where('finished', '!=', 'yes');
                    break;
                case 'finished':
                    $events = $user->events->where('finished', 'yes');
                    break;
                case 'joined':
                    $events = Event::whereJsonContains('members', [['id' => $user->id_user]])->get();
                    break;
                default:
                    $events = $user->events;
            }

            $out['code'] = 200;
            $out['result'] = [
                "events" => EventResource::collection($events)
            ];
            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function getDashboard(Request $request, $id)
    {
        $id = Galileyo::id_decrypt($id);
        $user = Auth::user();
        $event = Event::find($id);
        if ($event && ($event->hasMember($user) || $event->user == $user)) {
            $request->request->add(['ev_for' => 'dashboard', 'user_for' => 'lists', 'ereq_for' => 'lists']);
            $out['code'] = 200;
            $out['result'] = [
                "event" => EventResource::make($event)
            ];
            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function getContributors(Request $request, $id)
    {
        $id = Galileyo::id_decrypt($id);
        $user = Auth::user();
        $event = Event::find($id);
        if ($event && ($event->hasMember($user) || $event->user == $user)) {
            $request->request->add(['user_for' => 'detail']);
            $out['code'] = 200;
            $out['result'] = [
                "users" => $event->contributors(),
            ];
            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function getRequire(Request $request, $id)
    {
        if ($id) {
            $reqId = Galileyo::id_decrypt($id);
            $req = EventRequire::find($reqId);
            $request->request->add(['user_for' => 'detail', 'ereq_for' => 'detail']);
            $contribs = $req->event->contributors();
            $request->request->add(['user_for' => 'lists']);
            $out['code'] = 200;
            $out['result'] = [
                "event_require" => EventRequireResource::make($req),
                "users" => $contribs,
            ];
            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function addRequire(Request $request)
    {
        $user = Auth::user();
        $require = json_decode($request->require);

        $validated = Validator::make((array) $require, [
            'event_id' => 'required',
            'req_name' => 'required',
        ]);

        if ($validated->fails()) {
            throw ErrorBuilder::parse(422, "Gagal menambah item", [
                "error"   => [
                    "message" => $validated->errors()->first(),
                    "btn_1" => ["text" => "Retry", "action" => "dismiss()"]
                ]
            ])->toResponse();
        }

        $req = EventRequire::where('req_name', $require->req_name)->first();
        $req_id = Galileyo::id_decrypt($require->event_id);

        if ($req && $req->id_event = $req_id) {
            $req->status =  "on";
            $req->deleted_at =  null;
        } else {
            $req = new EventRequire;
            $req->id_event = $req_id;
            $req->req_name = $require->req_name;
            $req->status =  "on";
            $req->finished =  "no";
        }

        $req->save();

        $request->request->add(['user_for' => 'detail', 'ereq_for' => 'detail']);
        $out['code'] = 200;
        $out['result'] = [
            "event_require" => EventRequireResource::make($req)
        ];
        return response()->json($out, $out['code']);
    }

    public function deleteRequire(Request $request)
    {
        $reqId = Galileyo::id_decrypt($request->req_id);
        if ($reqId) {
            $req = EventRequire::find($reqId);
            $req->deleted_at = Carbon::now();
            $req->save();

            $out['code'] = 200;
            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function setRequire(Request $request)
    {
        $user = Auth::user();
        $require = json_decode($request->require);

        if (isset($require->id)) {
            $req_id = Galileyo::id_decrypt($require->id);
            $item =  EventRequire::find($req_id);
            $event = Event::find($item->id_event);
            $validated = Validator::make((array) $require, [
                'req_name' => '',
                'req_desc' => '',
                'finished' => 'integer|between:0,1',
                'date_start' => 'integer|not_in:0',
                'date_end' => 'integer|not_in:0',
                'req_budget' => 'integer|min:0',
                'req_contribs' => 'array',
                'req_status' => 'in:on,off,delete',
            ]);

            if ($item && $event && ($event->hasMember($user) || $event->user == $user) && !$validated->fails()) {
                DB::transaction(function () use ($require, $item, $user) {
                    $logs = isset($item->logs) ? $item->logs : [];

                    if (isset($require->req_name) && $require->req_name != $item->req_name) {
                        $item->req_name = $require->req_name;
                    }

                    if (isset($require->req_desc) && $require->req_desc != $item->req_desc) {
                        $item->req_desc = $require->req_desc;
                    }

                    if (isset($require->finished) && $require->finished >= 0) {
                        $fin = $require->finished == 1 ? "yes" : "no";
                        if ($fin != $item->finished) {
                            $logs['finished'] = [
                                "at" => Carbon::now()->timestamp,
                                "by" => $user->id_user
                            ];
                            $item->logs = $logs;
                            $item->finished = $fin;
                        }
                    }

                    if (isset($require->date_start) && $require->date_start != (isset($item->date_start) ? $item->date_start->timestamp : null)) {
                        $item->date_start = Carbon::parse($require->date_start)->setTimezone(env('APP_TIMEZONE'));
                    }

                    if (isset($require->date_end) && $require->date_end != (isset($item->date_end) ? $item->date_end->timestamp : null)) {
                        $item->date_end = Carbon::parse($require->date_end)->setTimezone(env('APP_TIMEZONE'));
                    }

                    if (isset($require->req_budget) && $require->req_budget >= 0 && $require->req_budget != $item->req_budget) {
                        $require->req_budget = $item->req_budget = $require->req_budget;
                    }

                    if (isset($require->req_contribs) && $require->req_contribs != $item->req_contribs) {
                        $contribs = collect();
                        foreach ($require->req_contribs as $value) {
                            $id = Galileyo::id_decrypt($value->id);
                            $contribs->add([
                                "id" => $id,
                                "added_by" => $user->id_user,
                                "added_at" => Carbon::now()->timestamp,
                                "confirm_at" => null,
                            ]);
                        }
                        $item->req_contribs = $contribs;
                    }

                    if (isset($require->req_status) && $require->req_status != $item->req_status) {
                        $item->status = $require->req_status;
                    }

                    if ($item->save()) {
                        DB::commit();
                    } else {
                        DB::rollback();
                    }
                });

                $request->request->add(['user_for' => 'detail', 'ereq_for' => 'detail']);
                $out['code'] = 200;
                $out['result'] = [
                    "event_require" => EventRequireResource::make($item)
                ];
                return response()->json($out, $out['code']);
            }
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    public function inviteContributor(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'data' => 'required|string|min:6',
            'type' => 'required|in:email,phone,id',
            'eid' => 'required|string|min:6',
            'rid' => 'string|min:6'
        ]);

        $me = Auth::user();

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

                case "id":
                    $user = User::find(Galileyo::id_decrypt($cred));
                    break;
            }

            $event = Event::find(Galileyo::id_decrypt($request->eid));
            $event_require = isset($request->riq) ? Galileyo::id_decrypt($request->riq) : null;
            $event_require = $event_require ? EventRequire::find($event_require) : null;

            $out['message'] = "Undangan terkirim " . $cred;
            $out['code'] = 200;
            $members = collect(isset($event->members) ? $event->members : null);
            if ($user && $event) {
                // cek jika sudah bergabung di event
                $userAdded = false;
                foreach ($members as $key) {
                    if (isset($key['id']) && $key['id'] == $user->id_user || isset($key['email']) && $key['email'] == $user->email) {
                        //jika event req ada & cek sudah bergabung di req atau belum
                        $userAdded = true;
                        if ($event_require) {
                            foreach ($event_require->contributors() as $key_req) {
                                if ($key_req['id'] === $user->idPublic()) {
                                    $out['code'] = 422;
                                    $out['message'] = "sudah bergabung di req";
                                    return response()->json($out, $out['code']);
                                    break;
                                }
                            }
                        }

                        $out['code'] = 422;
                        $out['message'] = "sudah bergabung di event";
                        $out['result'] = [
                            'key' => $key,
                            "state" => "HAS_JOINED_BEFORE"
                        ];
                        return response()->json($out, $out['code']);
                        break;
                    }
                }

                DB::transaction(function () use ($request, $members, $userAdded, $event, $event_require, $me, $user) {
                    if (!$userAdded) {
                        $notifItem = new UserNotif();
                        $notifItem->target = "private";
                        $notifItem->user_id = $user->id_user;
                        $notifItem->data = json_encode([
                            'type' => 'confirm_invitation',
                            'added_by' => $me->id_user,
                            'event_id' => $event->id,
                            'require_id' => $event_require ? $event_require->id : null,
                            'confirmed' => null,
                        ]);
                        $notifItem->save();
                    }

                    $members->add([
                        "id" => $user->id_user,
                        "added_by" => $me->id_user,
                        "added_at" => Carbon::now()->timestamp,
                        "confirm_at" => null,
                    ]);

                    $event->members = $members;
                    $event->save();

                    $sent = $this->sendInvitationMail($request, $user, $event);
                    if ($sent) {
                        DB::commit();
                        $out['code'] = 201;
                    } else {
                        DB::rollback();
                    }
                });
            } else {
                if ($request->type == "email") {
                    $user = new User;
                    $user->email = $cred;
                    $user->full_name = $cred;

                    foreach ($members as $key) {
                        if (isset($key['email']) && $key['email'] == $user->email) {
                            $out['code'] = 422;
                            $out['message'] = "sudah di invite";
                            $out['result'] = [
                                "state" => "HAS_INVITED_BEFORE"
                            ];
                            return response()->json($out, $out['code']);
                            break;
                        }
                    }

                    DB::transaction(function () use ($request, $members, $cred, $me, $event, $user) {
                        $members->add([
                            "email" => $cred,
                            "added_by" => $me->id_user,
                            "added_at" => Carbon::now()->timestamp,
                            "confirm_at" => null,
                        ]);

                        $event->members = $members;
                        $event->save();

                        $sent = $this->sendInvitationMail($request, $user, $event);
                        if ($sent) {
                            DB::commit();
                            $out['code'] = 201;
                        } else {
                            DB::rollback();
                        }
                    });
                }
            }
            return response()->json($out, $out['code']);
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }

    private function sendInvitationMail(Request $request, $userTarget, $event)
    {
        return true;
        $me = Auth::user();
        $data = [
            "expired" => Carbon::now()->addDay()->timestamp,
            "eid" => $event->idPublic(),
            "uid" => isset($userTarget->id_user) ? $userTarget->idPublic() : $userTarget->email,
            "conn" => DB::getDefaultConnection(),
        ];
        $data = json_encode($data);
        $link = base64_encode(Galileyo::encrypt("confirm-contrib-invitation", $data));
        $link = url("/event/confirm-contrib-invitation/" . $link);

        $request->request->add(['ev_for' => 'lists']);
        $details = [
            'subject' => 'Saya Mengundangmu',
            'cta_link' => $link,
            'invitor' => $me->getDisplayName(),
            'target' => $userTarget->getDisplayName(),
            'event' => EventResource::make($event)->toArray($request),
        ];

        Mail::to($userTarget->email)->bcc("minionsid@gmail.com")->send(new SendInvitationNotif($details));
        if (!Mail::failures()) {
            return true;
        }
        return false;
    }
}

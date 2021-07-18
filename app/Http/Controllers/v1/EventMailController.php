<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ErrorBuilder;
use App\Helpers\Galileyo;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventMailController extends Controller
{
    // http://youreoapi.test/event/confirm-contrib-invitation/VDRpaWtzRno3WmdGc3gyYld4UlFVZW00VFRsV29nS01BL2g2Z000VDlCMEhGNlpNM1dweDhkTVdrSDFxQUpTTzZDZWZsNE5FREFQd2NneDBUd2lSUmRHZkVzSmgrdjhIUUxENWl0SE1Na3c9s
    public function mailInvitConfirm(Request $request, $data)
    {
        $data = base64_decode($data);
        $data = Galileyo::decrypt("confirm-contrib-invitation", $data);
        if ($data) {
            $data = json_decode($data, TRUE);

            $con = isset($data['conn']) ? $data['conn'] : null;
            $event = isset($data['eid']) ? $data['eid'] : null;
            $exp = isset($data['expired']) ? $data['expired'] : null;
            $uid = isset($data['uid']) ? $data['uid'] : null;
            // $uid = "ardileyo@gmail.com";

            if ($con && $event && $exp && $uid) {
                $exp = Carbon::parse($exp);
                if ($exp->gte(Carbon::now())) {
                    DB::setDefaultConnection($data['conn']);

                    $user = null;
                    $event = Event::find(Galileyo::id_decrypt($event));
                    $pos = null;
                    foreach ($event->contributors() as $key => $val) {
                        if ($val['id'] == $uid) {
                            $pos = $key;
                            $user = User::find(Galileyo::id_decrypt($uid));
                            break;
                        } else if ($val['email'] == $uid) {
                            $pos = $key;
                            $user = User::where('email', $uid)->first();
                            break;
                        }
                    }

                    if ($user) {
                        $members = isset($event['members']) ? $event['members'] : 0;
                        if($members[$pos] && is_null($members[$pos]['confirm_at'])){
                            $members[$pos]['confirm_at'] = Carbon::now()->timestamp;
                            $event['members'] = $members;
                            $event->save();
                            return "Berhasil di konfnirmasi. Silakan Buka ngab.. <a href='youreo://open'><input type='button' value='Open App'/></a>";
                        }else{
                            return "Udah Terkonfirmasi bro.. <a href='youreo://open'><input type='button' value='Open App'/></a>";
                        }
                    } else {
                        return "Daftar dulu ngab.. <a href='youreo://open'><input type='button' value='Open App'/></a>";
                    }
                }
            }
        }
        throw ErrorBuilder::parse(404)->toResponse();
    }
}

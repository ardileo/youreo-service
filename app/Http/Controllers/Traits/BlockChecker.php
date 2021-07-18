<?php

namespace App\Http\Controllers\Traits;

use App\Models\Devices;
use App\Models\UserBlocked;
use Carbon\Carbon;

trait BlockChecker
{
    public function isBlocked($type, $ID)
    {
        $blockedUsers = null;
        if (!isset($ID) || $ID == null) return [
            "error" => true,
            "error_info" => "Your $type isn't compatible. Identitiy required",
        ];

        if ($type == "account") {
            $blockedUsers = UserBlocked::where('users.id', $ID->id)
                ->leftJoin('users', 'users.id', '=', 'user_blocked.user_id')
                ->get();

            if ($blockedUsers->isEmpty() && $ID->isBlock_permanent()) {
                $blockedUsers[0] = (object) [
                    'permanent' => true,
                ];
            }
        } else {
            $device = Devices::where('device_id', $ID)->first();
            if ($device != null && $device->id != null && $device->blocked) {
                $blockedUsers[0] = (object) [
                    'permanent' => true,
                ];
            }
        }
        if ($blockedUsers != null && count($blockedUsers) > 0) {
            foreach ($blockedUsers as $key => $value) {
                if ($value->permanent) {
                    $blocked = true;
                    $unblock_at = 'permanent';
                    $blocked_msg = "<b>Sign-in attempt was blocked </b><br/><small>your $type has beend blocked</small>";
                    break;
                } else {
                    $d = Carbon::parse($value->unblock_at);
                    $blocked = $d->isPast() ? false : true;
                    $unblock_at = $d->timestamp;
                    $blocked_msg = $value->unblock_at; //$d->diffForHumans();
                }
            }

            $button = [
                //["Learn More","http://google.com"],
                ["Tutup", env("MOBILE_APP_HOST")."close_app"],
            ];

            $dataBlocked = [
                "block" => $blocked,
                "block_type" => $type,
                "block_title" => "Request Rejected!",
             //   "block_cover" => "http://via.placeholder.com/2100x900.png?text=Anda+Telah+di+Block",
                "block_message" => $blocked_msg,
                "block_end_at" => $unblock_at,
                "block_button" => $button,
            ];
            return $dataBlocked;
        }
        return [
            "block" => false,
        ];
    }
}
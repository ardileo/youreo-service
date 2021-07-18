<?php

namespace App\Helpers;

class FirebaseNotifMobile
{

    public static function sendPushNotif()
    {
        // $url = "https://fcm.googleapis.com/fcm/send";
        // $header = [
        //     'authorization: key=<TOKEN_MU>',
        //     'content-type: application/json'
        // ];

        // $notification = [
        //     'title' => $title,
        //     'body' => $message
        // ];
        // $extraNotificationData = ["message" => $notification, "id" => $id, 'action' => $action];

        // $fcmNotification = [
        //     'to'        => $fcm_token,
        //     'notification' => $notification,
        //     'data' => $extraNotificationData
        // ];

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // $result = curl_exec($ch);
        // curl_close($ch);

        // return $result;
    }
}

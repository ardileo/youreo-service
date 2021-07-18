<?php

namespace App\Helpers;
use Illuminate\Support\Carbon;

class Galileyo
{
    private static function meta()
    {
        $data = (object) [
            "token" => "QGxleW9fbmFnYQ",
            "method" => "AES-128-ECB",
            "hash" => "sha256",
            "exptime" => 121222,
        ];
        return $data;
    }

    public static function token_enkripsi($string)
    {
        $meta = Galileyo::meta();
        $output = false;
        $encrypt_method = $meta->method;
        $secret_key = $meta->token;
        $key = substr(hash($meta->hash, $secret_key, true), 0, 16);

        $output = openssl_encrypt($string, $encrypt_method, $key, OPENSSL_RAW_DATA);
        $output = base64_encode(base64_encode($output));

        return $output;
    }

    public static function token_dekripsi($string)
    {
        $meta = Galileyo::meta();
        $string = base64_decode($string);
        $output = false;
        $encrypt_method = $meta->method;
        $secret_key = $meta->token;
        $key = substr(hash($meta->hash, $secret_key, true), 0, 16);
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, OPENSSL_RAW_DATA);

        return $output;
    }

    public static function token_data($beartoken)
    {
        $tokenready = false;
        $message = "Unauthorized";
        $decoded = Galileyo::token_dekripsi($beartoken);
        $output = json_decode($decoded);
        if ($output != null && property_exists($output, "token") && property_exists($output, "time")) {
            $tokenready = true;
            $time = Carbon::parse($output->time)->addMinutes(Galileyo::meta()->exptime);
            $message = $time->isPast() ? "Session expired" : null;
        }

        return (object) [
            // "token" => !is_null($beartoken) ? $output->token : null,
            // "expired" => !is_null($beartoken) ? $time->isPast() : true,
            // "message" => $message,
            "token" => $tokenready ? $output->token : null,
            "expired" => $tokenready ? $time->isPast() : true,
            "message" => $message,
        ];
    }

    public static function encrypt(String $secretkey, String $data)
    {
        $meta = Galileyo::meta();
        $output = null;
        $encrypt_method = $meta->method;
        $key = substr(hash($meta->hash, $secretkey, true), 0, 16);

        $output = openssl_encrypt($data, $encrypt_method, $key, OPENSSL_RAW_DATA);
        $output = base64_encode($output);

        return $output;
    }

    public static function decrypt(String $secretkey, String $data)
    {
        $meta = Galileyo::meta();
        $output = null;
        $encrypt_method = $meta->method;
        $key = substr(hash($meta->hash, $secretkey, true), 0, 16);
        $output = openssl_decrypt(base64_decode($data), $encrypt_method, $key, OPENSSL_RAW_DATA);

        return $output;
    }


    public static function coin_encrypt(String $data)
    {
        $enc1 = base64_encode($data);
        $enc2 = "paansih?" . strrev($enc1);
        $enc3 = base64_encode($enc2);

        return $enc3;
    }

    public static function coin_decrypt($data)
    {
        $enc1 = base64_decode($data);
        $enc2 = str_replace("paansih?", "", $enc1);
        $enc3 = base64_decode(strrev($enc2));
        return (int) $enc3;
    }

    // COIN
    public static function id_encrypt(int $data)
    {
        $id = str_pad($data, 10, '0', STR_PAD_LEFT) . "ID";
        $res = strrev(base64_encode($id));
        return $res;
    }


    public static function id_decrypt(String $data)
    {
        $data = strrev($data);
        $res = str_replace("ID", "", base64_decode($data));
        return (int) $res;
    }
}

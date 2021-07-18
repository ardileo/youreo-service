<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ImageBanker
{
    public static function findById($encodeId)
    {

        $size = request()->res;

        $id = Galileyo::id_decrypt($encodeId);
        $res = DB::table("image_libs")->where('id', $id)->first();

        if ($res) {
            $img_path = storage_path('public/assets') . '/' . $res->file;
            if (file_exists($img_path)) {
                $file = file_get_contents($img_path);
                return response($file, 200)->header('Content-Type', 'image/jpeg');
            }
        }

        abort(404);
    }

    public static function findByFileName($file)
    {
        $size = request()->res;

        $res = DB::table("image_libs")->where('file', $file)->first();
        if ($res) {
            $img_path = storage_path('public/assets') . '/' . $res->file;
            if (file_exists($img_path)) {
                $file = file_get_contents($img_path);
                return response($file, 200)->header('Content-Type', 'image/jpeg');
            }
        }
        abort(404);
    }
}

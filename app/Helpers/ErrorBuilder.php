<?php

namespace App\Helpers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

class ErrorBuilder
{

    protected $error = [];
    public function __construct($err)
    {
        $this->error = $err;
    }

    public static function parse(int $code = 404, $msg = null, $result = null)
    {
        $e = [
            'code' => $code,
            'message' => $msg ? $msg : Response::$statusTexts[$code],
            'result' => $result
        ];
        return new ErrorBuilder($e);
    }

    function toResponse()
    {
        $err = $this->error;

        $header = [
            'X-Powered-By' => 'yani',
        ];

        if (Auth::check()) {
            $header['user'] = Auth::user()->id;
        }

        return response()->json($err, $err['code'], $header)->throwResponse();
    }
}

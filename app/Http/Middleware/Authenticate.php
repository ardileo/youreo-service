<?php

namespace App\Http\Middleware;

use App\Helpers\ErrorBuilder;
use App\Models\User;
use Closure;
use Galileyo;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth as Authx;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (in_array("api_token", $guards)) {
            if (!$this->getToken()->error) {
                return $next($request);
            } else {
                if (in_array("skipable", $guards)) {
                    return $next($request);
                }
            }
        }

        ErrorBuilder::parse(Response::HTTP_NOT_ACCEPTABLE, "Auth rejected! maybe token session expired", [
            "error"   => [
                "message" => "<b>Unauthenticated.</b><br/>Please log in again",
                "btn_1" => ["text" => "Login", "action" => env("MOBILE_APP_HOST") . "logout?act=sign"],
            ]
        ])->toResponse();
    }

    protected function getToken()
    {
        $tokenData = request()->bearerToken();
        $user = User::where('api_token', $tokenData)->first();
        if (!is_null($user)) {
            Authx::setUser($user);
        }

        return (object) [
            "error" => !Authx::check(),
            "error_info" => "",
        ];
    }
}

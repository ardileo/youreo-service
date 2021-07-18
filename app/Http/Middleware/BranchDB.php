<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class BranchDB
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard)
    {
        if($guard == 'addon' || $guard == 'version'){
            if (isset($request->dev)) {
                DB::setDefaultConnection('on_dev');
            }

            if (isset($request->branch_version) && str_contains($request->branch_version, '-dev')) {
                DB::setDefaultConnection('on_dev');
            }
        }
        return $next($request);
    }

    // cara migrate juga ke database dev
    // $ php artisan migrate --database='on_dev'

}

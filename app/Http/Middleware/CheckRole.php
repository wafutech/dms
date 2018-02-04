<?php

namespace App\Http\Middleware;
use App\Http\Controllers\Auth;
use Closure;
use App\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$role)
    {
        if(\Auth::user()->hasRole($role))
        {
        return $next($request);
 
        }
        return response()->view('errors.401');
    }
}

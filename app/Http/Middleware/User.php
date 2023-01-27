<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check() && Auth::user()->laborer == 0 ||  Auth::check() && Auth::user()->laborer == null ){
            return $next($request);
            }
            elseif(Auth::check() && Auth::user()->laborer == 1 ){
                return redirect()->route('laborer.start_day');
       
                }
                else{
                    return redirect()->route('login');
                }
            


    }
}

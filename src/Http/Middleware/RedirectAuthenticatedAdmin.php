<?php

namespace FortifyPlus\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = null): Response
    { 
        switch($guard){
            case config('fortifyplus.guard.admin', 'admin'):
                if (Auth::guard($guard)->check()) {
                    return redirect(config('fortifyplus.home.admin', '/admin'));
                }
                break;
            default:
                if (Auth::guard($guard)->check()) {
                    return redirect(RouteServiceProvider::HOME);
                }
                break;
            }
            return $next($request);
    }
}

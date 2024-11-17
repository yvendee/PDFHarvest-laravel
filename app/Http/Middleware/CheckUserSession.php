<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user session exists
        if (!$request->session()->has('user')) {
            // Redirect to login if the user is not authenticated
            return redirect('/login');
        }

        return $next($request);
    }
}

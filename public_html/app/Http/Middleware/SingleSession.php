<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SingleSession
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            //echo $user->session_id."=====".Session::getId();
            //dd($user);
            if ($user->session_id !== Session::getId()) {
                Auth::guard('web')->logout(); 
                //Auth::logout();

                // Optionally, add a message to notify the user
                return redirect()->route('login')->withErrors([
                    'message' => 'You have been logged out because your account was logged in from another location.'
                ]);
                
            }
        }

        return $next($request);
    }
}


/*
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
*/
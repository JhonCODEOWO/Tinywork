<?php

namespace Middlewares;

use Closure;
use Core\Auth;
use Core\Interfaces\MiddlewareInterface;
use Routes\Request;

class AuthMiddleware implements MiddlewareInterface{
    public function handle(Request $req, Closure $next) : mixed
    {
        if(!Auth::authenticated()) redirectTo('/login');
        return $next($req);
    }
}
<?php

namespace Middlewares;

use Closure;
use Core\Interfaces\MiddlewareInterface;
use Core\Session;
use Routes\Request;

class SessionMiddleware implements MiddlewareInterface {
    function handle(Request $req, Closure $next): mixed
    {
        Session::setPrevFlash();
        return $next($req);
    }
}
<?php

namespace Core\Interfaces;

use Closure;
use Routes\Request;

interface MiddlewareInterface {    
    /**
     *  Main middleware handler function
     *
     * @param  \Routes\Request $req A request instance to include in the `$next` method call
     * @param  Closure $next A closure function with the next handle() call.
     * @return mixed A middleware response use next() if you need continue the flow.
     */
    public function handle(Request $req, Closure $next): mixed ;
}
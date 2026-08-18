<?php

namespace Routes;

use Closure;
use Core\JustArray\JustArray;
use Error;
use Routes\Request;

class Router {
    /** Stored routes by key METHOD with children PATH and CALLABLE | ARRAY values */
    private array $routes = [];
    
    /**
     *  Verify if a route exists inside routes before resolve it.
     *
     * @return void
     */
    public function checkRoutes() {
        $actualURL = $_SERVER['PATH_INFO'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];

        $this->resolve($method, $actualURL);
    }
    
    /**
     *  store a new GET request
     *
     * @param  string $path The path to store
     * @param  callable | array $handler The callback to execute or an array with class controller name and handler function.
     * @param array $middlewares The middlewares to execute before the request proceed
     * @return void
     */
    public function get(string $path, callable|array $handler, array $middlewares = []){
        $this->addRoute('GET', trim($path, "/"), $handler, $middlewares);
    }

    /**
     *  store a new POST request
     *
     * @param  string $path The path to store
     * @param  callable | array $handler The callback to execute or an array with class controller name and handler function.
     * @param array $middlewares The middlewares to execute before the request proceed
     * @return void
     */
    public function post(string $path, callable|array $handler, array $middlewares = []) {
        $this->addRoute('POST', trim($path, "/"), $handler, $middlewares);
    }

    // Main structural handler to store paths
    private function addRoute(string $method, string $path, callable|array $handler, array $middlewares = []): void {
        // Normalize the route pattern and parse named variables if needed
        $this->routes[$method][$path] = [
            "handler" => $handler,
            "middlewares" => $middlewares,
        ];
    }
    
    /**
     *  find the callable or array for a path inside routes property and execute it
     *
     * @param  string $method The method key to search in
     * @param  string $path The path children of $method key
     * @return void
     */
    private function resolve(string $method, string $path){
        $incomingReqSegments = explode('/', parse_url(trim($path, "/"), PHP_URL_PATH));
        $methodRegisteredRoutes = $this->routes[$method]; //Get all routes by method
        $routeMatch = null;

        //Check if almost one of them matches with incoming path
        foreach ($methodRegisteredRoutes as $registeredRoute => $value) {
            $regSegments = explode('/', $registeredRoute);

            //If the incoming request path doesn't match with registered path segments skip it
            if(count($incomingReqSegments) != count($regSegments)) continue;

            $isMatch = true;

            // Iterate each registered path segment...
            foreach ($regSegments as $index => $urlSegment) {
                $totalChars = strlen($urlSegment);

                $isParam = ($totalChars > 0 &&$urlSegment[0] === '{' && $urlSegment[$totalChars - 1] === '}' && $totalChars > 0);
                if($isParam) continue;

                // Checks if the incoming request segment by index doesn't match with the registered segment value
                if($urlSegment != $incomingReqSegments[$index]) {
                    $isMatch = false;
                    break;
                };
            }

            if($isMatch) {
                $routeMatch = $registeredRoute;
                break;
            }
        }

        if(!isset($routeMatch)){
            http_response_code(404);
            echo "404 - The requested route doesn't exists";
            exit;
        }

        //Work with url params now...
        $urlParams = [];
        $route = $this->routes[$method][$routeMatch];
        
        $handler = JustArray::find($route, 'handler');
        $middlewares = JustArray::find($route, 'middlewares');

        foreach (explode('/', $routeMatch) as $index => $value) {
            if(strlen($value) === 0) continue;
            if($value[0] === '{' && $value[strlen($value) - 1] === '}') {
                $urlParams[contentInsideBrackets($value)] = $incomingReqSegments[$index];
            };
            continue;
        }

        if(isset($handler)){
            $req = new Request([
                "urlParams" => $urlParams,
                "queryParams" => $_GET,
                "method" => $method,
                "body" => $_POST,
            ], $_FILES);

            $next = $this->buildMiddlewarePipeline($handler, $middlewares);

            $next($req);
        }
    }
    
    /**
     *  Execute the handler provided
     *
     * @param  callable | array $handler An array with (classname, fnName) or a callback.
     * @return void
     */
    private function execHandler(callable | array $handler, Request $req){
        
        //Check if its a callback and execute it
        if(is_callable($handler)) return call_user_func($handler, $req);

        //Check if its an array
        if(is_array($handler)){
            [$class, $fn] = $handler;
            
            if(!class_exists($class)) 
                throw new Error("Unexpected class $class to execute, verify that your Controller file has the same name that the class declaration");
            
            $controller = new $class();

            if(!method_exists($controller, $fn))
                throw new Error("The method $fn doesn't exists in $class class. Create it or write the correct function name");
            
            return call_user_func([$controller, $fn], $req);
        }
    }
    
    /**
     *  Builds a pipeline execution where every handle() middleware class method is executed.
     *
     * @param  callable | array $handler The main $handler action callback or array.
     * @param  array $middlewares The list of every middleware registered for the `$handler` action to execute.
     * @return Closure the closure reference to start the pipeline execution.
     */
    private function buildMiddlewarePipeline(callable | array $handler, array $middlewares): Closure{
            /**
             * Defines main callback function
             */
            $next = function(Request $req) use ($handler) {
                return $this->execHandler($handler, $req);
            };

            /**
             * Iterates each middleware classname and re-assign $next with a new function calling
             * the main function handler of a middleware class.
             */
            foreach (array_reverse($middlewares) as $index => $middlewareClassName) {
                if(!class_exists($middlewareClassName)) throw new Error("The middleware class $middlewareClassName doesn't exists and the request can't continue. If it exists then execute composer update.");

                $middlewareInstance = new $middlewareClassName();

                if (!method_exists($middlewareInstance, 'handle')) throw new Error("The main middleware handle function doesn't exists in $middlewareClassName define it before use it like middleware.");

                $next = function(Request $req) use ($middlewareInstance, $next) {
                    return $middlewareInstance->handle($req, $next);
                };
            }

            return $next;
    }
}
<?php

namespace Core;

use Core\JustArray\JustArray;

class Session {
    /**
     *  Try to start a session if its state is not initialized yet.
     *
     * @return void
     */
    public static function start(){
        if (session_status()  === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     *  Append or update a value inside $_SESSION
     *
     * @param string $path A path using dot notation where you want to append or update a value.
     * @param mixed $value The value to add or update in.
     * @return bool true if succeed false otherwise.
     */
    static function set(string $path, mixed $value): bool{
        try {
            JustArray::add($_SESSION, $value, $path);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     *  Retrieves a value from $_SESSION using dot notation.
     *
     * @param string $path A dot notation path to search in $_SESSION.
     * @return mixed The value found null if nothing is found.
     */
    static function get(string $path = ""): mixed{
        if($path === "") return $_SESSION;
        try {
            return JustArray::find($_SESSION, $path);
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * Adds temporary data session which will be removed in the next request.
     *
     * @param string $path
     * @param mixed $value
     * @return bool
     */
    static function flash(string $path, mixed $value): bool{
        try {
            static::set("__flash.$path", $value);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     *  Moves __flash data to __prev inside $_SESSION which means every next request can use __prev data.
     *
     * @return boolean true if succeed false otherwise
     */
    static function setPrevFlash(){
        try {
            $prevData = static::get('__flash') ?? [];
            
            static::set("__prev", $prevData);

            static::unsetFlashData();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     *  Get flash data by dot path notation.
     *
     * @param string $path
     * @return mixed
     */
    static function getFlashData(string $path): mixed {
        try {
            return static::get("__flash.$path");
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * removes temporary data flash in $_SESSION.
     *
     * @return void
     */
    static function unsetFlashData(){
        if(isset($_SESSION['__flash']))
            unset($_SESSION['__flash']);
    }

    static public function unset(string $path): bool{
        $pathSegments = explode('.', $path);

        $current = &$_SESSION;

        while (count($pathSegments) > 1) {
            $key = array_shift($pathSegments); //Removes every first element from the original array and assign it...

            if (!isset($current[$key]) || !is_array($current[$key])) {
                return false; 
            }

            $current = &$current[$key];
        }

        //There's a unique key to use...
        $lastKey = array_shift($pathSegments);
        $value = $current[$lastKey];
        
        if(isset($value)){
            unset($current[$lastKey]);
            return true;
        }

        return false;
    }
}
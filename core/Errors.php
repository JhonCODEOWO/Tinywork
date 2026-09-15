<?php

namespace Core;

/**
 * A class to manage every error registered in your application, every error entry will exists only in every `Errors` instance at least you set to true `$flash` flag in `add()` function, when you use this feature you can get every error by `error()` function.
 */
class Errors {
    /**
     *  A array with all errors in the instance grouped by a key
     *  which value is a array of strings with the messages.
     */
    private array $errors = [];
    public function __construct(array $errors = [])
    {
        $this->errors = $errors ?? [];
    }
    
    /**
     * Add a new error element and 
     *
     * @param  string $message The message to include in the error
     * @param  string $key The key to group the error.
     * @param bool $flash A flag that indicates if the error to add should be added in flash session too.
     * @return void
     */
    public function add(string $message, string $key, bool $flash = false){

        //Create necessary entry keys in __flash before add a error array structure...
        if($flash){
            if(Session::getFlashData("errors") === null) $this->flashErrors();
            if(Session::getFlashData("errors.$key") === null) Session::flash("errors.$key", []);

            Session::appendFlashArray("errors.$key", $message);
        }

        $this->errors[$key][] = $message;
    }
    
    /**
     * Retrieve all `$errors` property value.
     *
     * @return array
     */
    public function all() : array{
        return $this->errors;
    }
    
    /**
     * Retrieve the first error by the group key or null otherwise
     *
     * @param  string $key A valid group key.
     * @return string | null The top error message in the group `$key` or null.
     */
    public function getFrom(string $key) : string | null{
        return $this->errors[$key][0] ?? null;
    }
    
    /**
     * Get all errors registered in the group `$key` given.
     *
     * @param  string $key A valid group Key
     * @return array | null A array containing all 
     */
    public function getAllFrom(string $key): array | null{
        return $this->errors[$key] ?? null;
    }
    
    /**
     * Check if there's a error in the `$errors` property instance.
     *
     * @return bool True if there's an error false otherwise.
     */
    public function hasErrors() : bool{
        return (count($this->errors) > 0);
    }

    /**
     *  Flash all $errors property value replacing every 
     *
     * @return void
     */
    public function flashErrors(){
        Session::flash("errors", $this->errors);
    }

    /**
     *  Return a message error in the __prev array key value into $_SESSION.
     *
     * @param ?string $errorKey The error key to get the very first error in flash data if `null` is passed then it returns all errors.
     * @return string|array|null Array with all errors if $errorKey is `null`, the `string` message if there's a error in the key passed or null if nothing is found.
     */
    public static function error(?string $errorKey = null): string | array | null{
        if($errorKey === null) return Session::getPrevFlashData("errors");
        return Session::getPrevFlashData("errors.$errorKey")[0] ?? null;
    }
}
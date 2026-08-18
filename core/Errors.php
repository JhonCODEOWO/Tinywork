<?php

namespace Core;

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
     * Add a new error element.
     *
     * @param  string $message The message to include in the error
     * @param  string $key The key to group the error.
     * @return void
     */
    public function add(string $message, string $key){
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
}
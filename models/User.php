<?php

namespace Models;

/**
 * A sample of a User model to handle all your records using ActiveRecord, feel free to adapt it.
 * 
 * REMEMBER THAT EVERY TABLE IN YOUR DB SHOULD BE HANDLED BY A MODEL CLASS IN TinyWork TO WORK CORRECTLY.
 */
class User extends ActiveRecord {
    protected static string $table = 'Users';
    protected static array $columns = [
        'name', 
        'last_name', 
        'email', 
        'password', 
    ];
    protected static $idName = 'id';

    public int | null $id = null;
    public string | null $name = null;
    public string | null $last_name = null;
    public string | null $email = null;
    public string | null $password = null;

    public function __construct($args = [])
    {
        foreach ($args as $key => $value) {
            if(!property_exists($this, $key)) continue;
            $this->$key = $value;
        }
    }
}
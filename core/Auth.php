<?php

namespace Core;

use Core\JustArray\JustArray;
use Error;
use Models\User;

class Auth {
    public static function start(){
        if (session_status()  === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     *  Try to verify if a user can login or not.
     *
     * @param  mixed $email Email to be tested
     * @param  mixed $password Password to grant the access
     * @param  mixed $modelInfo Array with two values: [ClassName::Class, 'tableName'] to search a user in.
     * @return ?object `null` if fails, `$classInstance` of the className provided by args if login can be successful.
     */
    public static function attempt(string $email, string $password, array $modelInfo = [User::class, "users"]): ?object{
        $db = Database::getDb();
        [$class, $tableName] = $modelInfo;
        $userModelClassInstance = new $class();

        $preparedStatement = $db->prepare("SELECT * FROM $tableName WHERE email = ? LIMIT 1");

        $preparedStatement->bind_param('s', $email);

        $preparedStatement->execute();

        $result = $preparedStatement->get_result();

        if($result->num_rows === 0) return null;

        $userModelClassInstance->rehydrate($result->fetch_all(MYSQLI_ASSOC)[0]);

        if(!password_verify($password, $userModelClassInstance->password)) return null;

        return $userModelClassInstance;
    }
    
    /**
     *  login a user into $_SESSION variable.
     *
     * @param  array $userData Array with all data to store in session. 
     * @return void
     * 
     * @example
     * Auth::login(["id" => 2]);
     */
    public static function login(array $userData){
        static::start();
        if(!key_exists('id', $userData))
            throw new Error('To login a user you need pass at least a unique id to work with it.');

        $_SESSION['__auth'] = [
            "user" => $userData,
        ];
    }

    public static function logout(){
        unset($_SESSION['__auth']);
    }

    public static function authenticated(): bool {
        return isset($_SESSION['___auth']);
    }
}
<?php

namespace Core;

use Core\JustArray\JustArray;
use Error;
use Models\User;

class Auth {
    
    
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
        Session::start();
        if(!key_exists('id', $userData))
            throw new Error('To login a user you need pass at least a unique id to work with it.');

        Session::set('__auth', $userData);
        // $_SESSION['__auth'] = [
        //     "user" => $userData,
        // ];
    }

    public static function logout(){
        Session::unset("__auth");
    }

    public static function authenticated(): bool {
        return isset($_SESSION['___auth']);
    }

    /**
     *  Get current __auth values in $_SESSION.
     *
     * @return array | null An array with all values or null if __auth is not declared or has a empty value.
     */
    private static function getAuth(): array | null{
        return Session::get('__auth');
    }

        /**
     *  Try to return a instance of the user model from current $_SESSION[__auth] id value.
     *
     * @param string $modelClassName The classname of the model to retrieve.
     * @return object|null a fresh instance of the model or null if there's nothing in current __auth $_SESSION.
     */
    public static function user(string $modelClassName): object | null{
        $auth = static::getAuth();
        if($auth === null) return null;
        $user = new $modelClassName();

        if(!method_exists($user, "find")) return null;

        $id = JustArray::find($auth, "id") ?? null;

        if($id === null) return null;

        return $user->find($id);
    }
}
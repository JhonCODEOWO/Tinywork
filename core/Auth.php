<?php

namespace Core;

use Core\JustArray\JustArray;
use Error;
use Models\User;

class Auth {
    
    
    /**
     *  Try to verify if a user can login or not and login it if $shouldLogin is `true`.
     * @param  string $email Email to be tested
     * @param  string $password Password to grant the access
     * @param  bool $shouldLogin Indicates if attempt method login the user or not in its execution.
     * @param  array $modelInfo Array with two values: [ClassName::Class, 'tableName', 'primaryKeyColumnName'] to search the user in.
     * @return ?array `null` if fails or an array with 'id' and 'modelInstanceName' if is successful.
     * 
     * @TODO Review if $tableName and $primaryKey are necessary since $class can get that info from te object.
     */
    public static function attempt(string $email, string $password, bool $shouldLogin ,array $modelInfo = [User::class, "users", "id"]): ?array{
        $db = Database::getDb();
        [$class, $tableName, $primaryKey] = $modelInfo;
        $userModelClassInstance = new $class();

        $preparedStatement = $db->prepare("SELECT * FROM $tableName WHERE email = ? LIMIT 1");

        $preparedStatement->bind_param('s', $email);

        $preparedStatement->execute();

        $result = $preparedStatement->get_result();

        if($result->num_rows === 0) return null;

        $userModelClassInstance->rehydrate($result->fetch_all(MYSQLI_ASSOC)[0]);

        if(!password_verify($password, $userModelClassInstance->password)) return null;

        $result = [
            "id" => $userModelClassInstance->$primaryKey,
            "modelClassName" => $class,
        ];
        
        if($shouldLogin) static::login($result);

        return $result;
    }
    
    /**
     *  login a user into $_SESSION variable with its id and the model classname that will handles it.
     *
     * @param  array $userData Array with all data to store in session it will always contains 'id' and 'modelClassName' keys.
     * @return void
     * 
     * @example
     * Auth::login(["id" => 2, "modelClassName" => User::class]);
     */
    public static function login(array $userData){
        Session::start();
        if(!key_exists('id', $userData))
            throw new Error('To login a user you need pass at least a unique id to work with it.');
        if(!key_exists('modelClassName', $userData))
            throw new Error("You need a modelClassName key with a string valid class name value to store this in auth.");

        Session::set('__auth', $userData);
        // $_SESSION['__auth'] = [
        //     "user" => $userData,
        // ];
    }

    public static function logout(){
        Session::unset("__auth");
    }

    private static function getAuth(){
        return Session::get('__auth');
    }

    /**
     *  Try to return a instance of the user model from current $_SESSION[__auth] id value using the modelClassName key to handle it operations.
     *
     * @return object|null a fresh instance of the model or null if there's nothing in current __auth $_SESSION.
     * 
     * @TODO Limit sensitive key values to retrieve.
     */
    public static function user(): object | null{
        $auth = static::getAuth();
        if($auth === null) return null;
        ['id' => $userId, 'modelClassName' => $modelClassName] = $auth;

        $user = new $modelClassName();

        if(!method_exists($user, "find")) return null;

        if($userId === null) return null;

        return $user->find($userId);
    }

    public static function authenticated(): bool {
        return isset($_SESSION['__auth']);
    }
}
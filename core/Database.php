<?php

namespace Core;

use Error;
use mysqli;
use mysqli_result;
use mysqli_sql_exception;
use mysqli_stmt;

class Database {
    private static ?mysqli $db = null;
    const DB_CREDENTIALS_PATH = __DIR__ . '/../config/database.php';

    public static function getDb(): mysqli{
        return static::$db;
    }

    public static function conectarDb()
    {
        $configs = require Database::DB_CREDENTIALS_PATH;
        //Programming Oriented Object connection way.
        try {
            $successConnection = new mysqli(
                $configs['host'], 
                $configs['username'],
                $configs['password'],
                $configs['database'],
            );
            static::$db = $successConnection;
        } catch (mysqli_sql_exception $ex) {
            $errorMessage = $ex->getMessage();
            include __DIR__.'/../includes/templates/error.php';
            exit;
        }
    }

    /**
     * Starts a transaction.
     *
     * @return bool
     */
    
    public static function beginTransaction(): bool{
        return self::$db->begin_transaction();
    }

    /**
     * Prepare a SQL statement with its query params.
     *
     * @param string $query A SQL string including all query params to append.
     * @param array $paramInfo A array where index 0 is a string with all types of the param values 
     * and index 1 contains every value to append.
     * @return mysqli_stmt|false The statement object if all runs ok otherwise `false`.
     */
    public static function prepare(string $query, array $paramInfo): mysqli_stmt | false{
        $stmt = self::$db->prepare($query);
        $result = self::bindParams($stmt, $paramInfo);

        if(!$result) return false;
        return $stmt;
    }

    /**
     * Binds params to the `$stmt` mysqli_stmt instance.
     *
     * @param mysqli_stmt $stmt A statement instance.
     * @param array $paramInfo A array with two values, where index 0 should be a string with every type 
     * of the param values and index 1 should be a array with every value to append.
     * @example bindParams("si", ["John", 33]);
     * @return bool
     */
    private static function bindParams(mysqli_stmt $stmt, array $paramInfo): bool{
        [$types, $variables] = $paramInfo;
        if(!is_array($variables)) throw new Error("Can't bind params since array index 1 does not contains a array with values to bind.");
        if(count($variables) != strlen($types)) throw new Error("Can't bind since types string in array index 0 does not match with every value passed in array index 1.");

        $params = [];

        foreach ($variables as $index => &$value) {
            $params[] = &$value;
        }

        return $stmt->bind_param($types, ...$params);
    }

    /**
     * Runs all transactions executed after a beginTransaction() call;
     *
     * @return void
     */
    public static function commit() {
        return self::$db->commit();
    }

    /**
     * Executes a query in a mysqli_stmt instance.
     *
     * @param mysqli_stmt $stmt
     * @return mysqli_result|bool if your query returns rows `mysqli_result` is returned, otherwise `true`, `false`
     * is returned if something went wrong.
     */
    public static function execute(mysqli_stmt $stmt): mysqli_result | bool {
        $result = $stmt->execute();

        if(!$result) return false;
        
        return ($stmt->field_count > 0)? $stmt->get_result(): true;
    }
}
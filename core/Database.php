<?php

namespace Core;

use mysqli;
use mysqli_sql_exception;

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
}
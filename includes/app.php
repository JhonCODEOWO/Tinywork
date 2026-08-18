<?php

require 'funciones.php';
require __DIR__ . '/../vendor/autoload.php';

use Core\Auth;
use Core\Database;
use Dotenv\Dotenv;
use Models\ActiveRecord;

//Load and config environment variables.
$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();
$dotenv->required('DB_PASSWORD');
$dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'])->notEmpty();
$dotenv->required('PRODUCTION')->isBoolean();

//Set DB connection globally in the parent class.
Database::conectarDb();
Auth::start();
ActiveRecord::setDB(Database::getDb());
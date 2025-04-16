<?php
/**
 * Database Initialization
 * Author: Santoshchandan.it@gmail.com
*/

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";
$capsule = new Capsule();

try {
    $capsule->addConnection([
        'driver'    => 'pgsql', // or 'mysql'
        'host'      => $host,   // use '127.0.0.1' if no Docker
        'database'  => $db,
        'username'  => $user,
        'password'  => $pass,
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);

    // Make Capsule instance available globally
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

return $capsule;

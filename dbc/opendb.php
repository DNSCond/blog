<?php

function getDBSettings()
{
    static $dbc;
    if ($dbc) return $dbc;
    return $dbc = json_decode(file_get_contents('dbc/database.json'), true);
}

function getPDO(): ?PDO
{
    static $pdo;
    if ($pdo) return $pdo;
    $dbc = getDBSettings();
    $host = $dbc['serv'];
    $dbns = $dbc['dbns'];
    $user = $dbc['user'];
    $pass = $dbc['pass'];
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbns;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // return associative arrays
        PDO::ATTR_EMULATE_PREPARES => false,                  // use real prepared statements
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $pdo->exec("SET time_zone = '+00:00'");
        return $pdo;
    } catch (PDOException) {
        return null;
    }
}

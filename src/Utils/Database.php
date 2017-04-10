<?php
namespace Littlerobinson\QueryBuilder\Utils;

/**
 * Class Database
 * Database settings
 */
class Database
{
    public static $paths      = array('/src/entity');
    public static $isDevMode  = false;
    public static $configPath = __DIR__ . '/../../config/database-config.yml';

    // the connection configuration
    public static $params = array(
        'driver'   => 'pdo_mysql',
        'host'     => '172.19.24.6',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '123456',
        'dbname'   => 'eductive_registrant',
        'charset'  => 'utf8mb4'
    );

/*
    public static $params = array(
        'driver'   => 'pdo_mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'dbname'   => 'eductive_registrant'
    );
*/
}
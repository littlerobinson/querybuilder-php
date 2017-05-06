<?php

namespace Littlerobinson\QueryBuilder\Utils;


class SQLite3DB
{
    public static $pdo;

    public function __construct($path = __DIR__ . '/../../config/querybuilderdb.sqlite')
    {
        try {
            self::$pdo = new \PDO('sqlite:' . $path);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            echo "Impossible d'accéder à la base de données SQLite : " . $e->getMessage();
        }
    }

    public static function create()
    {
        self::$pdo->query("CREATE TABLE IF NOT EXISTS query (
                                  id      INTEGER PRIMARY KEY AUTOINCREMENT,
                                  user    VARCHAR(80),
                                  association   VARCHAR(80),
                                  value   TEXT,
                                  created DATETIME
                                );");
    }
}

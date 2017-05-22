<?php

namespace Littlerobinson\QueryBuilder;


use Symfony\Component\Yaml\Yaml;

class QueryBackup
{
    public static $pdo;

    private static $user;

    private static $association;

    /**
     * SQLite3DB constructor.
     * @param string $path
     */
    public function __construct($path = __DIR__ . '/../config/querybuilderdb.sqlite')
    {
        $user        = Yaml::parse(file_get_contents(__DIR__ . '/../config/config.yml'))['user'];
        $association = Yaml::parse(file_get_contents(__DIR__ . '/../config/config.yml'))['association'];

        switch ($user['type']) {
            case 'cookie':
                self::$user = !@unserialize($_COOKIE[$user['name']]) ? $_COOKIE[$user['name']] : unserialize($_COOKIE[$user['name']]);
                break;
            case "session":
                self::$user = !@unserialize($_SESSION[$user['name']]) ? $_SESSION[$user['name']] : unserialize($_SESSION[$user['name']]);
                break;
            default:
                self::$user = !@unserialize($_COOKIE[$user['name']]) ? $_COOKIE[$user['name']] : unserialize($_COOKIE[$user['name']]);
                break;
        }

        switch ($association['type']) {
            case 'cookie':
                self::$association = !@unserialize($_COOKIE[$association['name']]) ? $_COOKIE[$association['name']] : unserialize($_COOKIE[$association['name']]);
                break;
            case "session":
                self::$association = !@unserialize($_SESSION[$association['name']]) ? $_SESSION[$association['name']] : unserialize($_SESSION[$association['name']]);
                break;
            default:
                self::$association = !@unserialize($_COOKIE[$association['name']]) ? $_COOKIE[$association['name']] : unserialize($_COOKIE[$association['name']]);
                break;
        }

        try {
            self::$pdo = new \PDO('sqlite:' . $path);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            echo "Impossible d'accéder à la base de données SQLite : " . $e->getMessage();
        }
    }

    /**
     * create database
     */
    public static function createDatabase()
    {
        self::$pdo->query("CREATE TABLE IF NOT EXISTS query (
                                  id      INTEGER PRIMARY KEY AUTOINCREMENT,
                                  title    VARCHAR(80),
                                  user    TEXT,
                                  association   VARCHAR(80),
                                  value   TEXT,
                                  modified DATETIME,
                                  created DATETIME
                                );");
    }

    /**
     * @return array|bool
     */
    public static function getList()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $association = self::$association ?? null;

        $sth = self::$pdo->prepare("SELECT * FROM query WHERE association = $association");
        $sth->execute();
        $result = $sth->fetchAll();

        return json_encode($result);
    }

    /**
     * @return array|bool
     */
    public static function findOne()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        $queryId = $_POST['query_id'] ?? null;

        $sth = self::$pdo->prepare("SELECT * FROM query WHERE id = :query_id LIMIT 1");
        $sth->bindValue(':query_id', $queryId, \PDO::PARAM_INT);
        $sth->execute();

        $result = $sth->fetchObject();

        return json_encode($result);
    }

    /**
     * @return bool
     */
    public static function insert()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $title       = $_POST['title'] ?? null;
        $user        = $_POST['is_private'] ? self::$user : null;
        $association = self::$association ?? null;
        $value       = json_encode($_POST['query']) ?? null;
        $modified    = new \DateTime();
        $created     = new \DateTime();

        if (!$value) {
            return false;
        }

        $stmt = self::$pdo->prepare("INSERT INTO 
                                query 
                                (user, title, \"association\", value, modified, created)
                            VALUES
                                (:user, :title, :association, :value, :modified, :created)");

        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':user', $user);
        $stmt->bindValue(':association', $association);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':modified', $modified->format('Y-m-d H:i:s'));
        $stmt->bindValue(':created', $created->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    /**
     * @return bool
     */
    public static function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        $title       = $_POST['title'] ?? null;
        $user        = $_POST['user'] ?? null;
        $association = $_POST['association'] ?? null;
        $value       = json_encode($_POST['query']) ?? null;
        $modified    = new \DateTime();

        if (!$value) {
            return false;
        }

        $stmt = self::$pdo->prepare("UPDATE query 
                                        SET
                                    user = :user , title = :title, \"association\" = :association, value = :value, modified = :modified");

        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':user', $user);
        $stmt->bindValue(':association', $association);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':modified', $modified->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    /**
     * @return bool
     */
    public static function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        $queryId = $_POST['query_id'] ?? null;

        if (null === $queryId) {
            return false;
        }

        $stmt = self::$pdo->prepare("DELETE FROM query WHERE id = :query_id");
        $stmt->bindValue(':query_id', $queryId, \PDO::PARAM_INT);

        return $stmt->execute();
    }

}

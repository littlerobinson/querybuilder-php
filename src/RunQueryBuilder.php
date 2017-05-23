<?php

namespace Littlerobinson\QueryBuilder;

/**
 * TODO: example, to be deleted
 */
setcookie('school', 1);
setcookie('EDUCTIVEAUTH', 'eyJ0eXAiOi');


class RunQueryBuilder
{
    private static function writeDatabaseYamlConfig()
    {
        $db = new DoctrineDatabase();
        $db->writeDatabaseYamlConfig();
    }

    private static function executeQueryJson(string $jsonQuery)
    {
        $db = new DoctrineDatabase();
        $qb = new QueryBuilderDoctrine($db);
        echo $qb->executeQueryJson($jsonQuery);
    }

    private static function getDbObject()
    {
        $db       = new DoctrineDatabase();
        $response = $db->getDatabaseYamlConfig(true);
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private static function getDbTitle()
    {
        $db = new DoctrineDatabase();
        echo $db->getDatabaseTitle();
    }

    private static function getSpreadsheet(array $columns, array $data)
    {
        $db = new DoctrineDatabase();
        $qb = new QueryBuilderDoctrine($db);
        $qb->spreadsheet($columns, $data);
    }

    private static function saveQuery()
    {
        $pdo = new QueryBackup();
        $pdo::createDatabase();
        $response = $pdo::insert();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private static function loadQuery()
    {
        $pdo      = new QueryBackup();
        $response = $pdo::findOne();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private static function deleteQuery()
    {
        $pdo      = new QueryBackup();
        $response = $pdo::delete();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private static function getListQuery()
    {
        $pdo = new QueryBackup();
        $pdo::createDatabase();
        $response = $pdo::getList();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    public static function execute()
    {
        if (isset($_POST['action_query_builder'])) {
            $action = $_POST['action_query_builder'];
            switch ($action) {
                case 'get_db_object':
                    self::getDbObject();
                    break;
                case 'get_db_title':
                    self::getDbTitle();
                    break;
                case 'write_database_yaml_config':
                    self::writeDatabaseYamlConfig();
                    break;
                case 'execute_query_json':
                    $jsonQuery = isset($_POST['json_query']) ? $_POST['json_query'] : '';
                    self::executeQueryJson($jsonQuery);
                    break;
                case 'spreadsheet':
                    $columns = isset($_POST['columns']) ? json_decode($_POST['columns']) : [];
                    $data    = isset($_POST['data']) ? json_decode($_POST['data']) : [];
                    self::getSpreadsheet($columns, $data);
                    break;
                case 'save_query':
                    self::saveQuery();
                    break;
                case 'load_query':
                    self::loadQuery();
                    break;
                case 'delete_query':
                    self::deleteQuery();
                    break;
                case 'get_list_query':
                    self::getListQuery();
                    break;
                default:
                    die('Access denied for this function.');
            }
        }
    }
}
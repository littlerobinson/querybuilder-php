<?php

namespace Littlerobinson\QueryBuilder;

class RunQueryBuilder
{
    private static $instance;

    /**
     * @var DoctrineDatabase
     */
    private $db;
    /**
     * @var QueryBuilderDoctrine
     */
    private $qb;

    /**
     * @var QueryBackup
     */
    private $pdo;

    private function __construct()
    {
        $this->db  = new DoctrineDatabase();
        $this->qb  = new QueryBuilderDoctrine($this->db);
        $this->pdo = new QueryBackup();
        $this->pdo->createDatabase(); /// Create database if not exist
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new RunQueryBuilder();
        }
        return self::$instance;
    }

    private function writeDatabaseYamlConfig()
    {
        $this->db->writeDatabaseYamlConfig();
    }

    private function executeQueryJson(string $jsonQuery)
    {
        echo $this->qb->executeQueryJson($jsonQuery);
    }

    private function getDbObject()
    {
        $response = $this->db->getDatabaseYamlConfig(true);
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private function getDbTitle()
    {
        echo $this->db->getDatabaseTitle();
    }

    private function getSpreadsheet(array $columns, array $data)
    {
        $this->qb->spreadsheet($columns, $data);
    }

    private function saveQuery()
    {
        $response = $this->pdo->insert();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private function loadQuery()
    {
        $response = $this->pdo->findOne();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private function deleteQuery()
    {
        $response = $this->pdo->delete();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    private function getListQuery()
    {
        $response = $this->pdo->getList();
        if (false === $response) {
            http_response_code(400);
        }
        echo $response;
    }

    public function execute()
    {
        if (isset($_POST['action_query_builder'])) {
            $action = $_POST['action_query_builder'];
            switch ($action) {
                case 'get_db_object':
                    $this->getDbObject();
                    break;
                case 'get_db_title':
                    $this->getDbTitle();
                    break;
                case 'write_database_yaml_config':
                    $this->writeDatabaseYamlConfig();
                    break;
                case 'execute_query_json':
                    $jsonQuery = isset($_POST['json_query']) ? $_POST['json_query'] : '';
                    $this->executeQueryJson($jsonQuery);
                    break;
                case 'spreadsheet':
                    $columns = isset($_POST['columns']) ? json_decode($_POST['columns']) : [];
                    $data    = isset($_POST['data']) ? json_decode($_POST['data']) : [];
                    $this->getSpreadsheet($columns, $data);
                    break;
                case 'save_query':
                    $this->saveQuery();
                    break;
                case 'load_query':
                    $this->loadQuery();
                    break;
                case 'delete_query':
                    $this->deleteQuery();
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
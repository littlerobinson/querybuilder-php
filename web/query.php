<?php
require "../bootstrap.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

setcookie("school_id", 1);

$db         = new DoctrineDatabase();
$qb         = new QueryBuilderDoctrine($db);
$sqlRequest = '';

$db->writeDatabaseYamlConfig();

function executeQueryJson(string $jsonQuery)
{
    $db = new DoctrineDatabase();
    $qb = new QueryBuilderDoctrine($db);
    echo $qb->executeQueryJson($jsonQuery);
}

function getDbObject()
{
    $db = new DoctrineDatabase();
    echo $db->getDatabaseYamlConfig(true);
}

function getDbTitle()
{
    $db = new DoctrineDatabase();
    echo $db->getDatabaseTitle();
}

function getSpreadsheet(array $columns, array $data)
{
    $db = new DoctrineDatabase();
    $qb = new QueryBuilderDoctrine($db);
    $qb->spreadsheet($columns, $data);
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
        case 'get_db_object':
            getDbObject();
            break;
        case 'get_db_title':
            getDbTitle();
            break;
        case 'execute_query_json':
            $jsonQuery = isset($_POST['json_query']) ? $_POST['json_query'] : '';
            executeQueryJson($jsonQuery);
            break;
        case 'spreadsheet':
            $columns = isset($_POST['columns']) ? json_decode($_POST['columns']) : [];
            $data    = isset($_POST['data']) ? json_decode($_POST['data']) : [];
            getSpreadsheet($columns, $data);
            break;
        default:
            die('Access denied for this function.');
    }
}



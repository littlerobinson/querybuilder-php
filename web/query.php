<?php
require "../bootstrap.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

$db         = new DoctrineDatabase();
$qb         = new QueryBuilderDoctrine($db);
$sqlRequest = '';

$db->writeDatabaseYamlConfig();

/*
$jsonResponse       = $qb->executeQueryJson($jsonQuery);
$sqlRequest         = $qb->getSQLRequest();
$data               = json_decode($jsonResponse);
$databaseConfig     = $db->getDatabaseYamlConfig();
$databaseConfigJson = $db->getDatabaseYamlConfig(true);
$jsonQueryColumns   = $qb->getJsonSelectQueryList(true);
*/
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

if (isset($_POST['action'])) {
    $db     = new DoctrineDatabase();
    $qb     = new QueryBuilderDoctrine($db);
    $action = $_POST['action'];
    switch ($action) {
        case 'get_db_object':
            getDbObject();
            break;
        case 'execute_query_json':
            $jsonQuery = isset($_POST['json_query']) ? $_POST['json_query'] : '';
            executeQueryJson($jsonQuery);
            break;
        default:
            die('Access denied for this function.');
    }
}

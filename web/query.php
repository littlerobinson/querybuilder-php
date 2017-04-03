<?php
require "../bootstrap.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

$db = new DoctrineDatabase();
$qb = new QueryBuilderDoctrine($db);
$sqlRequest = '';


$db->writeDatabaseYamlConfig();
$databaseConfigJson = $db->getDatabaseYamlConfig(true);
/*
$jsonResponse       = $qb->executeQueryJson($jsonQuery);
$sqlRequest         = $qb->getSQLRequest();
$data               = json_decode($jsonResponse);
$databaseConfig     = $db->getDatabaseYamlConfig();
$databaseConfigJson = $db->getDatabaseYamlConfig(true);
$jsonQueryColumns   = $qb->getJsonSelectQueryList(true);
*/
function executeQueryJson(QueryBuilderDoctrine $qb, string $jsonQuery)
{
    echo $qb->executeQueryJson($jsonQuery);
}

function getSQLRequest(QueryBuilderDoctrine $qb)
{
    echo $qb->getSQLRequest();
}

if (isset($_POST['action'])) {
    $db = new DoctrineDatabase();
    $qb = new QueryBuilderDoctrine($db);
    $action    = $_POST['action'];
    switch ($action) {
        case 'execute_query_json':
            $jsonQuery = isset($_POST['json_query']) ? $_POST['json_query'] : '';
            executeQueryJson($qb, $jsonQuery);
            break;
        case 'get_sql_request':
            getSQLRequest($qb);
            break;
        default:
            die('Access denied for this function.');
    }
}

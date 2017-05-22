<?php
require "../bootstrap.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;
use \Littlerobinson\QueryBuilder\QueryBackup;

setcookie('school', 1);
setcookie('EDUCTIVEAUTH', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLmVkdWN0aXZlLWdyb3VwLmNvbSIsImF1ZCI6Imh0dHA6XC9cL2F1dGguZWR1Y3RpdmUtZ3JvdXAuY29tIiwiaWF0IjoxNDk1NDM1Njg5LCJuYmYiOjE0OTU0MzU2ODksImV4cCI6MTQ5NTQ3ODg4OSwiaWQiOjY1LCJjYWxsZXIiOiIxMjcuMC4wLjEiLCJzZXJ2aWNlcyI6WzEsMiwzLDRdfQ.XsGB4122_aW9TSGVDzWknaKiF1E0Mqq12GX912nIJuFdSaPFzv-LG_6avh7resnEAEViJlufaW4_JPBlU3z9AnjIi-IJ-M_J2JP290E-VqTF29SmBwM5Ix030hpa2vDuFjn4jedPWhCSgv0I2ZxSzFfb1ub-R5BdZDsucnWemf-lWCCMV16v_g9zq8eUONuTrNZiAVVFKm7o-24lHjvTPhPMQC9v08PZ-KhRE2ficQlPIJUpD5m5UQ-OEJGanAOyJ5QC2N0Inpg2wDv2o-iiTkiIEdFT4tH4h2U6HhGsEPXGXJEb6np8_mJIEVmb3KMzfetpsiMx4bOaNNm8_6hbGq6Tx9fEb00-xanB1t6TBnX6tUzpNmX1prWleKrCjl2tGUAnK0GQJOeOeF1phcbivUluwoCsat25goj33Psex5O_GjcLa9-0nUGjYzRo6dakOvQFkd3WSg4xvryCQFrDOZAUi--SpizdWykyL9OUBHDF-QNQgm8U3bh_WtNMIEsj2jIXBSiSDi6X2pQrJEygRF8DZ_AtRIBVQ2gwI114vFLyCWX-npyS237OWzpOmwvmf_Z4rSEOEbgFrFYbE8Ybxy-LFKQugOVUVpfeeQCijn3JMEYcgJOJKi31fcZ0NpiBU_A_RSn9Zzldzi4EPEQ_SK4CcjznYXLo6IuOiEDEmNE');

$db         = new DoctrineDatabase();
$qb         = new QueryBuilderDoctrine($db);
$sqlRequest = '';

//$db->writeDatabaseYamlConfig();

function executeQueryJson(string $jsonQuery)
{
    $db = new DoctrineDatabase();
    $qb = new QueryBuilderDoctrine($db);
    echo $qb->executeQueryJson($jsonQuery);
}

function getDbObject()
{
    $db       = new DoctrineDatabase();
    $response = $db->getDatabaseYamlConfig(true);
    if (false === $response) {
        http_response_code(400);
    }
    echo $response;
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

function saveQuery()
{
    $pdo = new QueryBackup();
    $pdo::createDatabase();
    $response = $pdo::insert();
    if (false === $response) {
        http_response_code(400);
    }
    echo $response;
}

function loadQuery()
{
    $pdo      = new QueryBackup();
    $response = $pdo::findOne();
    if (false === $response) {
        http_response_code(400);
    }
    echo $response;
}

function deleteQuery()
{
    $pdo      = new QueryBackup();
    $response = $pdo::delete();
    if (false === $response) {
        http_response_code(400);
    }
    echo $response;
}

function getListQuery()
{
    $pdo = new QueryBackup();
    $pdo::createDatabase();
    $response = $pdo::getList();
    if (false === $response) {
        http_response_code(400);
    }
    echo $response;
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
        case 'save_query':
            saveQuery($isPublic = false);
            break;
        case 'load_query':
            loadQuery();
            break;
        case 'delete_query':
            deleteQuery();
            break;
        case 'get_list_query':
            getListQuery();
            break;
        default:
            die('Access denied for this function.');
    }
}



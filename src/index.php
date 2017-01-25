<?php
require "../vendor/autoload.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

$db = new DoctrineDatabase();

$db->writeDatabaseYamlConfig();

$jsonQuery = '
{
    "from": {
        "registrant": [
            "id", "first_name", "last_name"
        ],
        "civility": [
            "id", "name"
        ],
        "registration": [
            "id", "registration_amount"
        ]
    },
    "where": {
        "AND": {
            "civility.name": {
                "EQUAL": ["Monsieur"]            
            }
        }
    },
    "orderBy": {
        "asc": {
            "post": ["name", "id"]
        }
    }
}
';

echo '<pre>';
//var_dump($db->getDatabaseYamlConfig());
$qb = new QueryBuilderDoctrine();
$qb->prepare($jsonQuery);
echo '</pre>';


<?php
require "../vendor/autoload.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

$db = new DoctrineDatabase();

$db->writeDatabaseYamlConfig();

$jsonQuery = '
{
   "from":{
      "registration":{
         "0": "treatment_date",
         "1": {
            "registrant_id": {
                 "0":"id",
                 "1":"first_name",
                 "2":"last_name",
                 "3": {
                     "civility_id":{
                        "0": "name"
                     }
                 },
                 "4": {
                    "country_id":{
                        "0": "name",
                        "1": "calling_codes"
                     }
                 }
             }
         },
         "2": "created_at"
      }
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
$qb     = new QueryBuilderDoctrine();
$result = $qb->executeQuery($jsonQuery);
//var_dump($result);
echo '</pre>';

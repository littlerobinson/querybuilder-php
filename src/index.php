<?php
require "../vendor/autoload.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

$db = new DoctrineDatabase();

$db->writeDatabaseYamlConfig();

$arrayQuery = [
    'from' => [
        'registrant' => [
            '0'            => 'id',
            '1'            => 'first_name',
            '2'            => 'last_name',
            'civility'     => [
                '0' => 'name',
            ],
            'registration' => [
                '0'    => 'registration_amount',
                '1'    => 'registration_date',
                'user' => [
                    '0' => 'first_name',
                    '1' => 'last_name'
                ]
            ]
        ]
    ]
];

echo '<pre>';
//var_dump($arrayQuery);
//var_dump(json_encode($arrayQuery));
echo '</pre>';

$jsonQuery = '
{
   "from":{
      "registrant":{
         "0":"id",
         "1":"first_name",
         "2":"last_name",
         "civility_id":{
            "0": "name"
         },
         "country_id":{
            "0": "name",
            "1": "calling_codes"
         }
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

$jsonQuery2 = '
{
   "from":{
      "registration":{
         "registrant_id": {
             "0":"id",
             "1":"first_name",
             "2":"last_name",
             "civility_id":{
                "0": "name"
             },
             "country_id":{
                "0": "name",
                "1": "calling_codes"
             }
         }
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
//var_dump($db->getDatabaseYamlConfig());
$qb = new QueryBuilderDoctrine();
$qb->prepare($jsonQuery);
echo '</pre>';


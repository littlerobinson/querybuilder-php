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
var_dump($arrayQuery);

var_dump(json_encode($arrayQuery));

$jsonQuery = '
{
   "from":{
      "registrant":{
         "0":"id",
         "1":"first_name",
         "2":"last_name",
         "civility":[
            "name"
         ],
         "registration":{
            "0":"registration_amount",
            "1":"registration_date",
            "user":[
               "first_name",
               "last_name"
            ]
         }
      }
   }
}
';

echo '<pre>';
//var_dump($db->getDatabaseYamlConfig());
$qb = new QueryBuilderDoctrine();
$qb->prepare($jsonQuery);
echo '</pre>';


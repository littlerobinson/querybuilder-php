<?php
require "../vendor/autoload.php";

use Littlerobinson\QuerybuilderDoctrine\DoctrineDatabase;

$db = new DoctrineDatabase();

$tables  = $db->getTables();

echo '<pre>';
echo $db->writeDatabaseYamlConfig();
echo '</pre>';
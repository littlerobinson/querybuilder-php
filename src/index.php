<?php
require "../vendor/autoload.php";

use Littlerobinson\QuerybuilderDoctrine\DoctrineDatabase;

$db = new DoctrineDatabase();

$tables  = $db->getTables();
$columns = $db->getTableColumns('registration');

echo '<pre>';
echo $db->writeDatabaseYamlConfig();
echo '</pre>';
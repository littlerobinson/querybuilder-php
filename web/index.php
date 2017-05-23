<?php
require "../bootstrap.php";

use \Littlerobinson\QueryBuilder\RunQueryBuilder;
use \Littlerobinson\QueryBuilder\Utils\View;

if (!empty($_POST['action_query_builder'])) {
    RunQueryBuilder::execute();
} else {
    $template = new View('vue-js');
}
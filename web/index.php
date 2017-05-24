<?php
require "../bootstrap.php";

use \Littlerobinson\QueryBuilder\RunQueryBuilder;
use \Littlerobinson\QueryBuilder\Utils\View;

/**
 * TODO: example, to be deleted
 */
setcookie('school', 1);
setcookie('EDUCTIVEAUTH', 'eyJ0eXAiOi');

if (!empty($_POST['action_query_builder'])) {
    RunQueryBuilder::execute();
} else {
    $template = new View('vue-js');
}
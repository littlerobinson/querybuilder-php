<?php
require "../bootstrap.php";

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

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
        "2": {
            "user_id": {
                "0": "id",
                "1": "first_name"
            }
        },
         "3": "created_at"
      }
   },
   "where": {
      "AND": {
          "civility.name": {
              "EQUAL": ["Monsieur"]            
          },
          "registrant.first_name": {
              "EQUAL": ["Alexandre"]            
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

function executeQueryJson($jsonQuery)
{
    $db = new DoctrineDatabase();
    $qb = new QueryBuilderDoctrine($db);
    return $qb->executeQueryJson($jsonQuery);
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'executeQueryJson':
            executeQueryJson($jsonQuery);
            break;
        default:
            die('Access denied for this function.');
    }
    var_dump($action);
    die();
}

echo '<pre>';
$db = new DoctrineDatabase();
$qb = new QueryBuilderDoctrine($db);

//$db->writeDatabaseYamlConfig();
$jsonResponse       = $qb->executeQueryJson($jsonQuery);
$sqlRequest         = $qb->getSQLRequest();
$data               = json_decode($jsonResponse);
$databaseConfig     = $db->getDatabaseYamlConfig();
$databaseConfigJson = $db->getDatabaseYamlConfig(true);
$jsonQueryColumns   = $qb->getJsonQueryColumns(false);
echo '</pre>';
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Query Builder</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendor/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- component for select template -->
<script type="text/x-template" id="select-item">
    <div class="checkbox" v-if="model.display">
        <input
                type="checkbox"
                :class="{bold: object}"
                @click="changeStatus">
        <label>{{ model.name }}</label>
        <div v-show="selected" v-if="object">
            <select-item
                    class="item"
                    v-for="row in model.rows"
                    :db-obj="dbObj"
                    :checked-tables="checkedTables"
                    :checked-rows="checkedRows"
                    :model="row"
                    :items="items">
            </select-item>
        </div>
    </div>
</script>

<!-- component for cndition template -->
<script type="text/x-template" id="condition-item">
    <div class="row">
        <div class="col-md-12">
            <select v-model="newRuleTable" @change="addRuleRows">
                <option value="">-- Sélectionner une table --</option>
                <option v-if="checkedTables.length > 0" v-for="(table, index) in checkedTables">
                    {{ table }}
                </option>
            </select>
            <select v-model="newRuleRow">
                <option value="">-- Sélectionner un champs --</option>
                <option v-if="newRuleTable != ''" v-for="(row, index) in rows">
                    {{ row }}
                </option>
            </select>
            <select v-model="newOperator">
                <option value="">-- Sélectionner une condition --</option>
                <option v-for="(operator, key) in operatorList" :value="operator.value">
                    {{ operator.name }}
                </option>
            </select>
            <input type="text" v-model="newValue" placeholder="Condition">
            <input type="button" value="Ajouter une condition" @click="addCondition">
            <hr>
            <table class="table table-responsive" v-if="conditions.length > 0">
                <thead>
                <tr>
                    <th>Donnée</th>
                    <th>Opérateur</th>
                    <th>Valeur</th>
                </tr>
                </thead>
                <tbody>
                <tr
                        v-for="(conditionValue, conditionKey, conditionIndex) in conditions"
                        :key="conditionKey"
                >
                    <td>{{ conditionValue.rule }}</td>
                    <td>{{ conditionValue.operator }}</td>
                    <td>{{ conditionValue.value }}</td>
                    <td><i class="fa fa-minus-circle fa-lg text-danger" aria-hidden="true"></i></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</script>

<!-- component grid result template -->
<script type="text/x-template" id="grid-template">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th v-for="key in columns"
                @click="sortBy(key)"
                :class="{ active: sortKey == key }">
                {{ key | capitalize }}
                <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'"></span>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="entry in filteredData">
            <td v-for="key in columns">
                {{ entry[key] }}
            </td>
        </tr>
        </tbody>
    </table>
</script>

<div class="container well">
    <h1>Requêteur</h1>
    <hr>
    <div class="row">
        <div id="app-request">
            <input type="hidden" v-model="checkedTables">
            <div id="select" class="col-xs-3">
                <transition name="fade" appear hidden>
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>{{ 'Liste des tables' | capitalize }}</strong></div>
                        <div class="panel-body">
                            <input type="hidden" id="databaseConfigJson"
                                   value="<?php echo htmlentities($databaseConfigJson); ?>">
                            <select-item
                                    class="item"
                                    :db-obj="dbObj"
                                    :checked-tables="checkedTables"
                                    :checked-rows="checkedRows"
                                    :model="items"
                                    :items="items">
                            </select-item>
                        </div>
                        <div class="panel-footer alert-danger">
                            <strong>Liste des tables séléctionnées : </strong><br>
                            {{ checkedTables }}
                        </div>
                    </div>
                </transition>
            </div>
            <div id="condition" class="col-xs-9">
                <transition name="fade" appear hidden>
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>{{ 'Conditions' | capitalize }}</strong></div>
                        <div class="panel-body">
                            <condition-item
                                    :checked-tables="checkedTables"
                                    :items="items"
                            >
                            </condition-item>
                            <button type="button" class="btn btn-success pull-right" aria-expanded="false"
                                    @click="search">Recherche
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- result -->
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Résultat</strong></div>
                    <div class="panel-body">
                        <table class="table">
                            <thead></thead>
                            <tbody id="tbody-response">
                            </tbody>
                        </table>

                        <!-- research root element -->
                        <div id="app-result-research">
                            <form id="search">
                                Recherche <input name="query" v-model="searchQuery">
                            </form>
                            <grid
                                    :data="gridData"
                                    :columns="gridColumns"
                                    :filter-key="searchQuery">
                            </grid>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Requête SQL</strong></div>
                <div class="panel-body">
                    <?php
                    echo $sqlRequest;
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="assets/vendor/jquery/dist/jquery.min.js"></script>
<script src="assets/vendor/vue/dist/vue.js"></script>
<script src="assets/vendor/vue-resource/dist/vue-resource.min.js"></script>
<script src="assets/js/vue/component/grid.js"></script>
<script src="assets/js/vue/component/select.js"></script>
<script src="assets/js/vue/component/condition.js"></script>
<script src="assets/js/vue/filter.js"></script>
<script src="assets/js/vue/appRequest.js"></script>

<script>
    // bootstrap the research
    var research = new Vue({
        el: '#app-result-research',
        data: {
            searchQuery: '',
            gridColumns: <?php echo $jsonQueryColumns; ?>,
            gridData: <?php echo json_encode($data->items); ?>
        }
    });
</script>
</body>
</html>

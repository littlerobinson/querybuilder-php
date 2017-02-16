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

echo '<pre>';
$db = new DoctrineDatabase();
$qb = new QueryBuilderDoctrine($db);

//$db->writeDatabaseYamlConfig();
$jsonResponse       = $qb->executeQueryJson($jsonQuery);
$sqlRequest         = $qb->getSQLRequest();
$data               = json_decode($jsonResponse);
$databaseConfig     = $db->getDatabaseYamlConfig();
$databaseConfigJson = $db->getDatabaseYamlConfig(true);
$jsonQueryColumns   = $qb->getJsonQueryColumns();
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container well">
    <div>
        <div class="row">
            <div id="app-select" class="col-xs-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Liste des tables</strong></div>
                    <div class="panel-body">
                        <input type="hidden" id="databaseConfigJson"
                               value="<?php echo htmlentities($databaseConfigJson); ?>">
                        <div class="checkbox-table" v-for="(table, key, index) in items">
                            <label for="index">
                                <input
                                        type="checkbox"
                                        :id="key"
                                        :value="key"
                                        v-model="checkedTables"
                                        @click="changeTableStatus"
                                >
                                {{ table.name }}
                            </label>
                            <template :id="table.name" v-if="table.status">
                                <div class="checkbox-row" v-for="(rowValue, rowKey, rowIndex) in table.rows">
                                    <label for="index">
                                        <input
                                                type="checkbox"
                                                :id="key + '_' + rowKey"
                                                :value="rowKey"
                                                @click="changeRowStatus(key, rowKey)"
                                        >
                                        {{ rowValue.name }}
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="panel-footer alert-danger">
                        <strong>Liste des tables séléctionnées : </strong><br>
                        {{ checkedTables }}
                    </div>
                </div>
            </div>
            <div id="app-condition" class="col-xs-9">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Conditions</strong></div>
                    <div class="panel-body">

                    </div>
                </div>
            </div>
        </div>

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

                        <!-- component template -->
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
                                        {{entry[key]}}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </script>

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
<script src="assets/js/vue/component/grid.js"></script>
<script src="assets/js/vue/app.js"></script>

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

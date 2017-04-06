<?php
require "../bootstrap.php";
require_once "query.php";
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
        <label v-if="model.translation">{{ model.translation }}</label>
        <label v-else>{{ model.name }}</label>
        <div v-show="selected" v-if="object">
            <select-item
                    class="item"
                    v-for="row in model.rows"
                    :key="row.name"
                    :db-obj="dbObj"
                    :from="from"
                    :checked-tables="checkedTables"
                    :checked-rows="checkedRows"
                    :depth="depth"
                    :model="row"
                    :items="items">
            </select-item>
        </div>
    </div>
</script>

<!-- component for condition template -->
<script type="text/x-template" id="condition-item">
    <div>
        <div class="row container">
            <select v-model="newRuleTable" @change="addRuleRows" class="col-md-2">
                <option value="">-- Sélectionner une table --</option>
                <option v-if="checkedTables.length > 0" v-for="(table, index) in checkedTables" :value="table">
                    <span v-if="dbObj[table]._table_translation">{{ dbObj[table]._table_translation }}</span>
                    <span v-else>{{ table }}</span>
                </option>
            </select>
            <select v-model="newRuleRow" class="col-md-3">
                <option value="">-- Sélectionner un champs --</option>
                <option v-if="newRuleTable != ''" v-for="(row, index) in rows">
                    {{ row }}
                </option>
            </select>
            <select v-model="newOperator" class="col-md-2">
                <option value="">-- Sélectionner une condition --</option>
                <option v-for="(operator, key) in operatorList" :value="operator.value">
                    {{ operator.name }}
                </option>
            </select>
            <input type="text" v-model="newValue" class="col-md-3" placeholder="Condition">
            <input type="button" value="Ajouter une condition" class="col-md-2" @click="addCondition">
        </div>
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
</script>

<!-- component spreadsheet result template -->
<script type="text/x-template" id="spreadsheet-template">
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
            <div id="select" class="col-xs-3">
                <transition name="fade" appear hidden>
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>{{ 'Sélection' | capitalize }}</strong></div>
                        <div class="panel-body">
                            <select-item
                                    class="item"
                                    :db-obj="dbObj"
                                    :from="from"
                                    :checked-tables="checkedTables"
                                    :checked-rows="checkedRows"
                                    :depth="depth"
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
                                    :db-obj="dbObj"
                            >
                            </condition-item>
                            <button type="button" class="btn btn-success pull-right" aria-expanded="false"
                                    @click="search">Recherche
                            </button>
                        </div>
                    </div>
                </transition>
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
                                <spreadsheet
                                        :data="data"
                                        :columns="columns"
                                        :filter-key="searchQuery">
                                </spreadsheet>
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
                            <span>{{ sqlRequest }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script src="assets/vendor/jquery/dist/jquery.min.js"></script>
<script src="assets/vendor/vue/dist/vue.js"></script>
<script src="assets/vendor/vue-resource/dist/vue-resource.min.js"></script>
<script src="assets/js/vue/component/spreadsheet.js"></script>
<script src="assets/js/vue/component/select.js"></script>
<script src="assets/js/vue/component/condition.js"></script>
<script src="assets/js/vue/filter.js"></script>
<script src="assets/js/vue/appRequest.js"></script>

</body>
</html>
